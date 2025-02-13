<?php

// ExampleController.php

namespace App\Http\Controllers;

use App\Services\MuqeemApiService;
use Illuminate\Support\Facades\File;

class InteractiveServicesController extends Controller
{
    protected $muqeemApiService;

    public function __construct(MuqeemApiService $muqeemApiService)
    {
        $this->muqeemApiService = $muqeemApiService;
    }
    /**
     * This service allows either an organization to issue an exit re-entry visa for one of its resident,
     *ER visa has two types, single entry or multiple entries, also, the ER visa can be of a specific
     *duration in days, or a specific date the resident must return to the kingdom before it.
     */
    public function issueExitReEntryVisa($iqamaNumber, $visaDuration)
    {
        try {
            $body_data = [
                "iqamaNumber" => $iqamaNumber,
                "visaType" => 1,
                "visaDuration" =>  $visaDuration,
            ];
            $report = $this->muqeemApiService->callApiEndpoint('api/v1/exit-reentry/issue', 'POST', $body_data);

            // Retrieve the base-64 encoded PDF string from the response
            $base64Pdf = $report['ervisaPDF'];

            // Decode the base-64 string
            $pdfContent = base64_decode($base64Pdf);

            $directoryPath = public_path('uploads/muqeem/exit_reentry');

            // Ensure the directory exists
            if (!File::exists($directoryPath)) {
                File::makeDirectory($directoryPath, 0755, true);
            }

            $filePath = $directoryPath . '/' . $iqamaNumber . '.pdf';

            // Save the PDF content to the defined file path
            file_put_contents($filePath, $pdfContent);

            // Check if the file was created
            if (!File::exists($filePath)) {
                throw new \Exception('Failed to save the PDF file.');
            }

            return [
                'success' => 1,
                'message' => 'Request Issue Exit Re-Entry Visa Successful',
                'file_path' => 'muqeem/exit_reentry/' . $iqamaNumber . '.pdf',
                'data' => $report,
            ];
        } catch (\Exception $e) {
            // Log the error for debugging purposes
            \Log::error('Error in issueExitReEntryVisa: ' . $e->getMessage());
            return ['success' => 0, 'error' => $e->getMessage()];
        }
    }


    //This service allows an organization to cancel an exit re-entry visa for one of its resident.
    public function cancleExitReEntryVisa()
    {
        try {
            $body_data = [
                "iqamaNumber" => "2000000000",
                "erVisaNumber" => "1365896",
            ];
            $report = $this->muqeemApiService->callApiEndpoint('api/v1/exit-reentry/cancel', 'POST', $body_data);
            return response()->json([
                'message' => 'Request Cancle Exit Re-Entry Visa Successful',
                'data' => $report,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'full_error' => $e->getResponse()->getBody()->getContents(),
            ], 500);
        }
    }

    //This service allows an organization to cancel an exit re-entry visa for one of its resident.
    public function reprintExitReEntryVisa()
    {
        try {
            $body_data = [
                "iqamaNumber" => "2000000000",
                "visaNumber" => "1365896",
            ];
            $report = $this->muqeemApiService->callApiEndpoint('api/v1/exit-reentry/reprint', 'POST', $body_data);
            return response()->json([
                'message' => 'Request Reprint Exit Re-Entry Visa Successful',
                'data' => $report,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'full_error' => $e->getResponse()->getBody()->getContents(),
            ], 500);
        }
    }

    //This service allows an organization to issue a final exit visa for one of its resident.
    public function issueFinalExitVisa($iqamaNumber)
    {

        try {
            $body_data = [
                "iqamaNumber" => $iqamaNumber,
            ];
            $report = $this->muqeemApiService->callApiEndpoint('api/v1/final-exit/issue', 'POST', $body_data);
            return [
                'success' => 1,
                'message' => 'Request Final Exit Visa Successful',
                'data' => $report,
            ];
        } catch (\Exception $e) {
            return [
                'success' => 0,
                'error' => $e->getMessage(),
                'full_error' => $e->getResponse()->getBody()->getContents(),
            ];
        }
    }

    //This service allows an organization to cancel a final exit visa for one of its resident.
    public function cancleFinalExitVisa()
    {
        try {
            $body_data = [
                "iqamaNumber" => "2000000000",
                "feVisaNumber" => "25174946",
            ];
            $report = $this->muqeemApiService->callApiEndpoint('api/v1/final-exit/cancel', 'POST', $body_data);
            return response()->json([
                'message' => 'Request Cancle Final Exit Visa Successful',
                'data' => $report,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'full_error' => $e->getResponse()->getBody()->getContents(),
            ], 500);
        }
    }

    /**
     * This service allows an organization to update one of its resident’s passport information by
     *extending the passport expiry date to a new one.
     */
    public function extendPassportValidity()
    {
        try {
            $body_data = [
                "iqamaNumber" => "2000000000",
                "passportNumber" => "1523698",
                "newPassportExpiryDate" => "2026-07-09",
            ];
            $report = $this->muqeemApiService->callApiEndpoint('api/v1/update-information/extend', 'POST', $body_data);
            return response()->json([
                'message' => 'Request Extend Passport Validity Successful',
                'data' => $report,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'full_error' => $e->getResponse()->getBody()->getContents(),
            ], 500);
        }
    }

    //This service allows an organization to renew one of its resident’s passport information.
    public function renewPassport()
    {
        try {
            //newPassportIssueLocation => the name of the city from the international cities lookup.
            $body_data = [
                "iqamaNumber" => "2000000000",
                "passportNumber" => "1523698",
                "newPassportNumber" => "1598741",
                "newPassportIssueDate" => "2024-07-09",
                "newPassportExpiryDate" => "2026-07-09",
                "newPassportIssueLocation" => "3",
            ];
            $report = $this->muqeemApiService->callApiEndpoint('api/v1/update-information/renew', 'POST', $body_data);
            return response()->json([
                'message' => 'Request Renew Passport  Successful',
                'data' => $report,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'full_error' => $e->getResponse()->getBody()->getContents(),
            ], 500);
        }
    }

    //This service allows an organization to renew one of its resident’s iqama.
    public function renewIqama($iqamaNumber, $iqamaDuration)
    {
        try {
            $body_data = [
                "iqamaNumber" => $iqamaNumber,
                "iqamaDuration" => $iqamaDuration,
            ];
            //  $body_data = [
            //     "iqamaNumber" => "2000000000",
            //     "iqamaDuration" => "12",
            // ];
            $report = $this->muqeemApiService->callApiEndpoint('api/v1/iqama/renew', 'POST', $body_data);
            return response()->json([
                'message' => 'Request Renew Iqama  Successful',
                'data' => $report,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'full_error' => $e->getResponse()->getBody()->getContents(),
            ], 500);
        }
    }

    //This service allows an organization to Issue Iqama to one of its visitor’s.
    public function issueIqama()
    {
        try {
            $body_data = [
                "borderNumber" => "3216572077",
                "iqamaDuration" => "12",
                "lkBirthCountry" => "311",
                "maritalStatus" => "1",
                "passportIssueCity" => "أمستردام",
                "trFirstName" => "tyuu",
                "trFatherName" => "ertt",
                "trGrandFatherName" => "rrr",
                "trFamilyName" => "yyuu",
            ];
            $report = $this->muqeemApiService->callApiEndpoint('api/v1/iqama/issue', 'POST', $body_data);
            return response()->json([
                'message' => 'Request Issue Iqama  Successful',
                'data' => $report,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'full_error' => $e->getResponse()->getBody()->getContents(),
            ], 500);
        }
    }

    /**This service allows an organization to extend visit visa for one of its visitor’s.
    For specific visit visa types (Business visit, Medical visit, Governmental visit, Businessmen
    visit, Business visit) */
    public function extendVisitVisa()
    {
        try {
            $body_data = [
                "borderNumber" => "3216572077",
            ];
            $report = $this->muqeemApiService->callApiEndpoint('api/v1/visit-visa/extend', 'POST', $body_data);
            return response()->json([
                'message' => 'Request Extend Visit Visa Successful',
                'data' => $report,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'full_error' => $e->getResponse()->getBody()->getContents(),
            ], 500);
        }
    }

    /**This service allows an organization to Issue Final Exit during the Probationary Period for specific
    visitor */
    public function issueFinalExitDuringProbationaryPeriod()
    {
        try {
            $body_data = [
                "borderNumber" => "3216572077",
                "lkBirthCountry" => "311",
                "maritalStatus" => "1",
                "passportIssueCity" => "أمستردام",
                "trFirstName" => "tyuu",
                "trFatherName" => "ertt",
                "trGrandFatherName" => "rrr",
                "trFamilyName" => "yyuu",
            ];
            $report = $this->muqeemApiService->callApiEndpoint('api/v1/final-exit/issue/probation-period', 'POST', $body_data);
            return response()->json([
                'message' => 'Request issue Final Exit During Probationary Period Successful',
                'data' => $report,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'full_error' => $e->getResponse()->getBody()->getContents(),
            ], 500);
        }
    }

    /* This service allows an organization to transfer Iqama for specific resident to organization after
    taking the approval by MOL*/
    public function iqamaTransfer()
    {
        try {
            $body_data = [
                "iqamaNumber" => "2139800201",
                "newSponsorId" => "7100000970",
            ];
            $report = $this->muqeemApiService->callApiEndpoint('api/v1/iqama/transfer', 'POST', $body_data);
            return response()->json([
                'message' => 'Request Iqama TransferSuccessful',
                'data' => $report,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'full_error' => $e->getResponse()->getBody()->getContents(),
            ], 500);
        }
    }

    /* This service allows organization to check MOL approval before preforming change occupation for
    specific resident under organization sponsorship*/
    public function checkMolApproval()
    {
        try {
            $body_data = [
                "iqamaNumber" => "2139800201",
            ];
            $report = $this->muqeemApiService->callApiEndpoint('api/v1/occupation/check-mol-approval', 'POST', $body_data);
            return response()->json([
                'message' => 'Request check Mol Approval Successful',
                'data' => $report,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'full_error' => $e->getResponse()->getBody()->getContents(),
            ], 500);
        }
    }

    /* This service allows organization to change occupation for specific resident under organization
    sponsorship after MOL approval */
    public function changeOccupation()
    {
        try {
            $body_data = [
                "iqamaNumber" => "2139800201",
            ];
            $report = $this->muqeemApiService->callApiEndpoint('api/v1/occupation/change', 'POST', $body_data);
            return response()->json([
                'message' => 'Request Change Occupation Successful',
                'data' => $report,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'full_error' => $e->getResponse()->getBody()->getContents(),
            ], 500);
        }
    }

    /* This service allows an organization to extend an exit re-entry visa for one of its residents currently
    outside the kingdom only. */
    public function extendReentry()
    {
        try {
            $body_data = [
                "iqamaNumber" => "2140746161",
                "visaDuration" => "11",
                "visaNumber" => "25176165"
            ];
            $report = $this->muqeemApiService->callApiEndpoint('api/v1/exit-reentry/extend', 'POST', $body_data);
            return response()->json([
                'message' => 'Request Extend Exit Re-Entry VisaSuccessful',
                'data' => $report,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'full_error' => $e->getResponse()->getBody()->getContents(),
            ], 500);
        }
    }
}
