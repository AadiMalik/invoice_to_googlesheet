<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Google\Client;
use Google\Service\Sheets;
use GuzzleHttp\Client as HttpClient;
use Smalot\PdfParser\Parser;

class InvoiceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if ($user->role == 'user')
            $invoices = Invoice::with('user')->where('user_id', $user->id)->get();
        else
            $invoices = Invoice::with('user')->get();


        return view('invoice.index', compact('invoices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('invoice.create');
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        ini_set('max_execution_time', 0);

        $request->validate([
            'invoice' => 'required|mimes:jpg,jpeg,png,pdf',
        ]);

        try {
            DB::beginTransaction();

            // Upload image
            $path = $request->file('invoice')->store('uploads', 'public');
            $filePath = storage_path('app/public/' . $path);

            if ($request->file('invoice')->getClientOriginalExtension() === 'pdf') {
                $parser = new Parser();
                $pdf = $parser->parseFile($filePath);
                $text = $pdf->getText();
            } else {
                $text = (new TesseractOCR($filePath))
                    // for cpanel
                    // ->executable('/usr/bin/tesseract')
                    // for local machine
                    ->executable('C:\Program Files\Tesseract-OCR\tesseract.exe')
                    ->lang('eng')
                    ->run();
            }

            // Generate unique invoice no
            $uniqueInvoiceNo = 'invoice_' . rand(10000, 99999);

            // Store in DB
            $invoice = new Invoice();
            $invoice->user_id = Auth::user()->id;
            $invoice->invoice_no = $uniqueInvoiceNo;
            $invoice->invoice = $path;
            $invoice->save();

            // ChatGPT prompt - only 12 fields
            $http = new \GuzzleHttp\Client();
            $prompt = "
You are an OCR invoice parser. Extract the following OCR text into valid JSON with EXACTLY these fields:

{
  \"date\": \"\",
  \"invoice_no\": \"\",
  \"vat_no\": \"\",
  \"brn\": \"\",
  \"company_from\": \"\",
  \"total_amount\": \"\",
  \"zero_rated\": \"\",
  \"exempt\": \"\",
  \"vat_amount\": \"\"
}

If a field is missing, return empty string. Only return the above fields.
OCR TEXT:
$text
";

            $response = $http->post('https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-4o-mini',
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a data extractor.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                ],
            ]);

            $result = json_decode($response->getBody(), true);
            $structured = json_decode($result['choices'][0]['message']['content'], true);

            // Google Sheets setup
            $googleClient = new Client();
            $googleClient->setApplicationName('Invoice Reader');
            $googleClient->setScopes([Sheets::SPREADSHEETS]);
            $googleClient->setAuthConfig(public_path(env('GOOGLE_SHEET_PATH')));
            $service = new Sheets($googleClient);

            $spreadsheetId = env('GOOGLE_SHEET_ID');
            $sheetName = 'Invoices1';

            // Check if sheet exists
            $spreadsheet = $service->spreadsheets->get($spreadsheetId);
            $sheetExists = false;

            foreach ($spreadsheet->getSheets() as $sheet) {
                if ($sheet->getProperties()->getTitle() === $sheetName) {
                    $sheetExists = true;
                    break;
                }
            }

            // Create sheet if not exists
            if (!$sheetExists) {
                $addSheetRequest = new \Google\Service\Sheets\AddSheetRequest([
                    'properties' => new \Google\Service\Sheets\SheetProperties(['title' => $sheetName])
                ]);
                $batchUpdateRequest = new \Google\Service\Sheets\BatchUpdateSpreadsheetRequest([
                    'requests' => [['addSheet' => $addSheetRequest]]
                ]);
                $service->spreadsheets->batchUpdate($spreadsheetId, $batchUpdateRequest);
            }

            // Append headings only if first row empty
            $existingValues = $service->spreadsheets_values->get($spreadsheetId, $sheetName . '!A1:L1');
            if (empty($existingValues->getValues())) {
                $headers = [[
                    'Date',
                    'Website Invoice No',
                    'Invoice No',
                    'VAT No',
                    'BRN',
                    'Supplier Name/Company Name',
                    'Total Amount',
                    'Zero Rated',
                    'Exempt',
                    'VAT Amount',
                    'Taxable',
                    'File ID',
                    'Unique Key'
                ]];
                $headerBody = new Sheets\ValueRange(['values' => $headers]);
                $service->spreadsheets_values->append(
                    $spreadsheetId,
                    $sheetName . '!A1:L1',
                    $headerBody,
                    ['valueInputOption' => 'RAW']
                );
            }

            // Prepare invoice row
            $rowData = [
                $structured['date'] ?? '',
                $uniqueInvoiceNo,
                $structured['invoice_no'] ?? '',
                'C' . $structured['vat_no'] ?? '',
                'C' . $structured['brn'] ?? '',
                $structured['company_from'] ?? '',
                $structured['total_amount'] ?? '',
                $structured['zero_rated'] ?? '',
                $structured['exempt'] ?? '',
                $structured['vat_amount'] ?? '',
                (float)$structured['vat_amount'] / 15,
                (string)($invoice->id ?? ''),
                uniqid()
            ];

            // Convert multi-line to single-line
            $rowData = array_map(function ($v) {
                return str_replace(["\n", "\r"], ' ', (string)$v);
            }, array_values($rowData));

            // Append invoice row (new row, Column A start)
            $body = new Sheets\ValueRange(['values' => [$rowData]]);
            $service->spreadsheets_values->append(
                $spreadsheetId,
                $sheetName . '!A:L',
                $body,
                ['valueInputOption' => 'RAW']
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect('invoice')->with('success', 'Invoice saved successfully with unique no & formatted!');
    }



    public function view($id)
    {
        $invoice = Invoice::findOrFail($id); // safe fetch
        $filePath = storage_path('app/public/' . $invoice->invoice);

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
            // ✅ Image OCR
            $text = (new TesseractOCR($filePath))
                // for cpanel
                // ->executable('/usr/bin/tesseract')
                // for local machine
                ->executable('C:\Program Files\Tesseract-OCR\tesseract.exe')
                ->lang('eng')
                ->run();
        } elseif ($extension === 'pdf') {
            // ✅ PDF → parse text
            $parser = new Parser();
            $pdf = $parser->parseFile($filePath);
            $text = $pdf->getText();

            if (trim($text) === '') {
                // ❌ Agar scanned PDF hai (image-based)
                throw new \Exception('This PDF is scanned (image-based). Please upload a text-based PDF.');
            }
        } else {
            throw new \Exception('Unsupported file type: ' . $extension);
        }

        // ✅ Send OCR text to ChatGPT for JSON parsing
        $http = new \GuzzleHttp\Client();

        $prompt = "
    You are an OCR invoice parser. 
    Extract the following text into **valid JSON** with this structure:

    {
      \"invoice_heading\": \"\",
      \"company_from\": \"\",
      \"customer_to\": \"\",
      \"invoice_no\": \"\",
      \"date\": \"\",
      \"items\": [
        {
          \"name\": \"\",
          \"qty\": \"\",
          \"price\": \"\",
          \"disc_percent\": \"\",
          \"disc_amount\": \"\",
          \"vat_tax\": \"\",
          \"total\": \"\"
        }
      ],
      \"totals\": {
        \"subtotal\": \"\",
        \"discount\": \"\",
        \"total_ex_vat_tax\": \"\",
        \"total_vat_tax\": \"\",
        \"net_total\": \"\"
      }
    }

    If a field is missing, leave it as an empty string. 
    OCR TEXT:
    $text
    ";

        $response = $http->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type'  => 'application/json',
            ],
            'json' => [
                'model' => 'gpt-4o-mini',
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a data extractor.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
            ],
        ]);

        $result = json_decode($response->getBody(), true);
        $structured = json_decode($result['choices'][0]['message']['content'], true);

        // ✅ Add invoice_no from DB (not random)
        $structured['unique_invoice_no'] = $invoice->invoice_no;

        return view('invoice.view', compact('structured'));
    }



    public function destroy($id)
    {
        $invoice = Invoice::find($id);
        $invoice->delete();

        return response()->json([
            'success' => true,
            'message' => 'invoice deleted successfully!'
        ]);
    }
}
