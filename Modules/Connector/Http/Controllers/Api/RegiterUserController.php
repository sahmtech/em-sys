<?php
namespace Modules\Connector\Http\Controllers\Api;

use App\Helper\Api\ApiResponse;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegiterUserController extends ApiController
{
    use ApiResponse;

    protected function register(Request $request)
    {

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
        $user->user_type   = 'admin';
        $user->username    = $request->username;
        $user->allow_login = true;
        $user->status      = true;
        $user->save();

        return self::apiResponse(200, 'created');

    }
}
