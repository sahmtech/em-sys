<?php
namespace Modules\Connector\Http\Controllers\Api;

use App\Helper\Api\ApiResponse;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegiterUserController extends ApiController
{
    use ApiResponse;

    protected function register(Request $request)
    {
        try {

            $rules = [
                'username' => 'required|string|max:255|unique:users,username',
                'password' => 'required',
            ];

            $validator = Validator::make($request->all(), $rules);
            $valArr    = [];
            if ($validator->fails()) {
                foreach ($validator->errors()->getMessages() as $key => $value) {
                    $errStr = $value[0];
                    array_push($valArr, $errStr);
                }

                return self::apiResponse(400, $valArr);
            }
            $user = new User();

            $user->password    = Hash::make($request->input('password'));
            $user->user_type   = 'employee';
            $user->first_name  = $request->username;
            $user->username    = $request->username;
            $user->allow_login = true;
            $user->status      = true;
            $user->save();

            return self::apiResponse(200, trans('api.user.create_success'));
        } catch (\Exception $e) {
            \Log::error('Error deleting user: ' . $e->getMessage());

            // Return an error response
            return self::apiResponse(500, trans('api.user.error'));
        }

    }

    protected function destroy(Request $request): JsonResponse
    {
        $user = auth()->user();

        if (! $user) {
            return self::apiResponse(401, trans('api.user.unauthorized'));
        }

        try {
            $user->delete();

            return self::apiResponse(200, trans('api.user.delete_success'));
        } catch (\Exception $e) {
            \Log::error('Error deleting user: ' . $e->getMessage());

            // Return an error response
            return self::apiResponse(500, trans('api.user.error'));
        }
    }

}
