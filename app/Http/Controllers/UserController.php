<?php

namespace App\Http\Controllers;

use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\Facades\Image as Image;
use Validator;

class UserController extends Controller
{
    /**
     * The request instance.
     *
     * @var \Illuminate\Http\Request
     */
    private $request;
    /**
     * Create a new controller instance.
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    /**
     * Create a new token.
     *
     * @param \App\User $user
     * @return string
     */
    protected function jwt(User $user)
    {
        $payload = [
            'iss' => 'lumen-jwt', // Issuer of the token
            'sub' => $user->id, // Subject of the token
            'iat' => time(), // Time when JWT was issued.
            'exp' => time() + 600000, // Expiration time
        ];
        return JWT::encode($payload, env('JWT_SECRET'), 'HS512');
    }
    /**
     * Authenticate a user and return the token if the provided credentials are correct.
     *
     * @param \App\User $user
     * @return mixed
     */

    /**
     * Registration method
     *
     * @param Request $request registration request
     *
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */

    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'success' => false,
                    'status' => 400,
                    'message' => $validator->errors()->all(),
                ],
            ]);
        }
        try {

            $hasher = app()->make('hash');

            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->provider = 'self';
            $user->provider_id = '';
            $user->password = $hasher->make($request->password);

            //Create account number using day, month, min and seconds with leading 00.
            $user->account_no = '00' . date('dmis');

            //  $user->image = $filename;
            $user->save();

            return json_encode([
                'result' => [
                    'success' => true,
                    'status' => 200,
                    'message' => 'Registration successful',
                    'user_data' => $user,
                    'token' => $this->jwt($user),
                ],
            ]);
        } catch (\Illuminate\Database\QueryException $ex) {
            return json_encode([
                'status' => 500,
                'success' => false,
                'message' => $ex->getMessage(),
            ]);
        }
    }

    public function redirect($service)
    {
        return Socialite::driver($service)->redirect();
    }

    public function callback($service)
    {
        $user = new User();
        $social_user = Socialite::with($service)->user();
        if ($service == 'Facebook') {
            $user->name = $social_user['name'];
            $user->email = $social_user['email'];

            $user->account_no = '00' . date('dmis');

            $user->save();

            return json_encode([
                'result' => [
                    'success' => true,
                    'status' => 200,
                    'message' => 'Registration successful',
                    'user_data' => $user,
                    'token' => $this->jwt($user),
                ],
            ]);
        }
        //  return view ( 'home' )->withDetails ( $user )->withService ( $service );
    }

    public function authenticate(User $user)
    {
        $validator = Validator::make(
            $this->request->all(),
            [
                'account_no' => 'required',
                'password' => 'required',
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'success' => false,
                    'status' => 400,
                    'message' => $validator->errors()->all(),
                ],
            ]);
        }
        $user = User::where('account_no', $this->request->input('account_no'))->first();
        if (!$user) {
            return response()->json([
                'error' => [
                    'message' => 'Account number does not exist.',
                    'status' => 400,
                ],
            ]);
        }
        if (Hash::check($this->request->input('password'), $user->password)) {
            return response()->json([
                'result' => [
                    'success' => true,
                    'message' => 'Successfully logged in',
                    'token' => $this->jwt($user),
                    'status' => 200,
                ],
            ]);
        }
        return response()->json([
            'error' => [
                'message' => 'Password is wrong.',
                'status' => 400,
            ],
        ]);
    }

    public function update_profile(Request $request)
    {

        $validator = Validator::make($request->all(), [

            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048|required',
            'dob' => 'required',
            'phone_number' => 'required',
            'address' => 'required',
            'type_of_id' => 'required',
            'url_of_id' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048|required',
            'type_of_add' => 'required',
            'url_of_add' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048|required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'success' => false,
                    'status' => 400,
                    'message' => $validator->errors()->all(),
                ],
            ]);
        }
        try {

            $user_id = app('request')->get('authUser')->id;
            $user = User::find($user_id);

            $user->dob = $request->dob;
            $user->phone_number = $request->phone_number;
            $user->address = $request->address;
            $user->type_of_id = $request->type_of_id;

            $user->type_of_add = $request->type_of_add;

            // Taking the images of the user... Creating a new folder for each customer on the server.
            if (($request->hasFile('image')) && ($request->hasFile('url_of_id')) && ($request->hasFile('url_of_add'))) {
                //URL path created for the user images' folder.
                $user->image = $request->image->storeAs($user->account_no, 'Profile_' . time() . '.' . $request->image->getClientOriginalExtension());
                $user->url_of_id = $request->url_of_id->storeAs($user->account_no, 'Identity_' . time() . '.' . $request->url_of_id->getClientOriginalExtension());
                $user->url_of_add = $request->url_of_add->storeAs($user->account_no, 'Address_' . time() . '.' . $request->url_of_add->getClientOriginalExtension());

            }

            $user->update();

            return json_encode([
                'result' => [
                    'success' => true,
                    'status' => 200,
                    'message' => 'Update successful',
                    'user_data' => $user,
                ],
            ]);
        } catch (\Illuminate\Database\QueryException $ex) {
            return json_encode([
                'status' => 500,
                'success' => false,
                'message' => $ex->getMessage(),
            ]);
        }
    }
}
