<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth as JWTAuth;


class AuthController extends Controller
{

    public function store(Request $request)
    {
        //membuat validasi
        $this->validate($request, [
            'name' =>  'required',
            'email' =>  'required',
            'password' =>  'required|min:5'
        ]);

        //membuat variable utk menerima request dr clien
        $name = $request->input('name');
        $email = $request->input('email');
        $password = $request->input('password');

        //membuat objek u/ membuat hash pass (gk boleh langsung/plain test)
        $user   = new User([
            'name'  =>  $name,
            'email'  =>  $email,
            'password'  =>  bcrypt($password)
        ]);
        //sebelum melakukan save user harus input email dan password
        $credentials = [
            'email' =>  $email,
            'password'  => $password
        ];
        //jika kondisi berhasil membuat data user ke db kita jg harus membuat trycatch (proses generate token) jika berhasil menampilkan token jk gagal akan menmpilkan response gagal

        //membuat respon ketika data sukses di save
        if ($user->save()) { //memberi kondisi validasi

            //==== membuat trycatch untuk mencocokan token
            $token = null;
            try {
                if (!$token = JWTAuth::attempt($credentials)) {
                    return response()->json([
                        'msg' => 'Email or Password are incorrect',
                    ], 404);
                }
            } catch (JWTException $e) {
                return response()->json([
                    'msg' => 'failed_to_create_token',
                ], 404);
            }

            $user->signin = [
                'href'  =>  'api/v1/user/signin',
                'method'    =>  'POST',
                'params'    =>  'email,password'
            ];

            //memberikan respon setelah validasi diatas terpenuhi
            $response   = [
                'msg'    =>   'user created',
                'user'  =>   $user,
                'token' => $token
            ];
            return response()->json($response, 201);
        }

        $response   = [
            'msg'   =>  'Erorr Bosque perikasa lagi data yg diinput'
        ];
        return response()->json($response, 404);
    }

    public function signin(Request $request)
    {
        //membuat validasi
        $this->validate($request, [
            'email' =>  'required|email',
            'password' =>  'required|min:5'
        ]);

        //membuat variable utk menerima request dr clien
        $email = $request->input('email');
        $password = $request->input('password');

        //membuat kondisi saat inputan email harus = db
        if ($user = User::where('email', $email)->first()) {
            $credentials = [
                'email' =>  $email,
                'password'  => $password
            ];

            //mengenerate token baru #token  register != token login(sudah expired)
            $token = null;
            try {
                if (!$token = JWTAuth::attempt($credentials)) {
                    return response()->json([
                        'msg' => 'Email or Password are incorrect',
                    ], 404);
                }
            } catch (JWTException $e) {
                return response()->json([
                    'msg' => 'failed_to_create_token',
                ], 404);
            }

            $response   = [
                'msg'    =>   'user signin',
                'user'  =>   $user,
                'token' => $token
            ];
            return response()->json($response, 201);
        }

        $response   = [
            'msg'   =>  'Erorr Bosque perikasa lagi data yg diinput'
        ];
        return response()->json($response, 404);
    }

    ///dek role melalui token
    public function role(Request $request)
    {
        return JWTAuth::toUser($request->header('token'));
    }
}
