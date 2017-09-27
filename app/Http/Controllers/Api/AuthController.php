<?php

namespace App\Http\Controllers\Api;

use App\User;

use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Validator;

use Auth;
use Tymon\JWTAuth\JWTAuth;
use Tymon\JWTAuth\JWTException;
use Tymon\JWTAuth\JWTAuthException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

use DB;

class AuthController extends Controller
{
    private $user;
    private $jwtauth;

    public function __construct(User $user, JWTAuth $jwtauth)
    {
      $this->user = $user;
      $this->jwtauth = $jwtauth;

      $this->middleware('jwt.auth', ['except' => ['login','register', 'logout']]);
    }

    public function register(RegisterRequest $request)
    {
        $bodyContent = (array) json_decode($request->getContent());

        $name = $bodyContent['name'];
        $login = $bodyContent['login'];
        $password =  $bodyContent['password'];

        $newUser = $this->user->create([
          'name' => $name,
          'login' => $login,
          'password' => bcrypt($password)
        ]);

        if (!$newUser) {
            return response()->json(['failed_to_create_new_user'], 500);
        }

        return response()->json([
            'token' => $this->jwtauth->fromUser($newUser)
        ], 200);
    }

    public function login(LoginRequest $request)
    {
      // get user credentials: email, password

        $bodyContent = (array) json_decode($request->getContent());

        $login = $bodyContent['login'];
        $password = $bodyContent['password'];

        $token = null;

        try 
        {
            $token = $this->jwtauth->attempt([
                'login'   => $login,
                'password'=> $password
                ]);

            if (!$token)
            {
              return response()->json(['invalid_email_or_password'], 422);
            }
        } 
        catch (JWTAuthException $e) 
        {
            return response()->json(['failed_to_login'], 500);
        }

        return response()->json([$token], 200);
    }

    public function logout(Request $request)
    {
        $token = $request->get('token');

        try 
        {
            $this->jwtauth->invalidate($token);
        }
        catch (JWTException $e) 
        {
            return response()->json(['failed_to_logout'], 500);
        }
        catch (TokenExpiredException $e) 
        {
            return response()->json(['failed_to_logout'], 500);
        }
        

        return response()->json([$token], 200);
    }

    public function index( )
    {
        $data = DB::table('data')->select('id', 'login', 'filename')->get();

        foreach ($data as $entry) {

            echo "( ".$entry->id." ) ".$entry->login." | ".$entry->filename."\n";
        }
    }

    public function upload(Request $request)
    {
        $token = $request->get('token');

        $file = $request->file('filename');

        if ($file)
        {
            $temp = file_get_contents($file);
            $blob = base64_encode($temp);

            DB::table('data')->insert(
                [
                'login' => (string)Auth::user()->login, 
                'token' => $token,
                'filename'=> $file->getClientOriginalName(),
                'data' => $blob
                ]
            );

            // $destinationPath = 'uploads';
            // $file->move($destinationPath,$file->getClientOriginalName());

            return response()->json([$file->getClientOriginalName(), $token], 200);
        }
        else
            return response()->json(["no file uploaded"], 200);
    }
}