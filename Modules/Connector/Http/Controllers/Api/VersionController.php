<?php
namespace Modules\Connector\Http\Controllers\Api;

use App\Helper\Api\ApiResponse;
use App\Version;
use Illuminate\Http\Request;

class VersionController extends ApiController
{
    use ApiResponse;

    public function __construct()
    {
        $this->middleware('auth:api', [
            'except' => ['checkUpdate'],
        ]);
    }

    protected function compareVersions($version1, $version2)
    {
        $version1Parts = explode('.', $version1);
        $version2Parts = explode('.', $version2);

        for ($i = 0; $i < 3; $i++) {
            $part1 = isset($version1Parts[$i]) ? (int) $version1Parts[$i] : 0;
            $part2 = isset($version2Parts[$i]) ? (int) $version2Parts[$i] : 0;

            if ($part1 < $part2) {
                return -1; // Version 1 is less than Version 2
            } elseif ($part1 > $part2) {
                return 1; // Version 1 is greater than Version 2
            }
        }

        return 0; // Versions are equal
    }

    protected function checkUpdate(Request $request)
    {

        $rules = [
            'os'      => 'required',
            'version' => 'required',
        ];
        $request->validate($rules, $request->all());
        $version = Version::where('os', $request->os)->first();
        if ($this->compareVersions($request->version, $version->version) >= 0) {

            $this->body['allowed']   = 1;
            $this->body['in_review'] = (bool) $version->in_review;
            return self::apiResponse(200, "allowed", $this->body);
        } else {
            $this->body['allowed']   = 0;
            $this->body['in_review'] = (bool) $version->in_review;

            return self::apiResponse(200, "Not allowed", $this->body);
        }
    }
}
