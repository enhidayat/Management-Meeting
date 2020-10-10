<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Meeting;

class RegisterController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //membuat registrasi meeting
        //1. membuat validasi
        $this->validate($request, [
            'meeting_id'    =>  'required',
            'user_id'   =>  'required'
        ]);

        //2. membuat variabel user id dan meeting id yg berisi data inputan sebelumnya
        $meeting_id = $request->input('meeting_id');
        $user_id = $request->input('user_id');

        //3. membuat masing2 objek model   untuk masing2 variabel agr memudahkan saat memanggil
        $meeting = Meeting::findOrFail($meeting_id); //$meeting_id didapat dari inputan user
        $user    = User::findOrFail($user_id);


        //4. membuat pesan untuk respon(message,data meeting,data user tambah elemenurl utk unregiter) 
        $message = [
            'msg'   =>  'User is already registered for meeting',
            'user'  =>  $user,
            'meeting'   =>  $meeting,
            'unregister'    =>  [
                'href'      =>  'api/v1/meeting/registration' . $meeting->id,
                'method'    =>  'DELETE'
            ]
        ];

        //5. membuat kondisi data usr_id n meeting_id sudah terdaftar belum jk blum buat data baru jk sudah ada (batalkan [404])

        // if ($meeting->users()->where('users.id', $user->id)->first()) {
        //     return response()->json($message, 404);
        // };

        if ($meeting->users()->where('users.id', $user->id)->first()) {
            return response()->json($message, 404);
        };

        //data belum ada = buat data baru
        //membuat  attacment dari data meeting inputan
        $user->meetings()->attach($meeting);
        // $user->meetings()->attach($meeting);

        $response = [
            'msg' => 'User registered for meeting',
            'meeting' => $meeting,
            'user' => $user,
            'unregister' => [
                'href' => 'api/v1/meeting/registration/' . $meeting->id,
                'method' => 'DELETE'
            ]
        ];

        return response()->json($response, 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //membuat Unregister user meeting
        $meeting = Meeting::findOrFail($id);
        $meeting->users()->detach();

        $response = [
            'msg'   =>  'User unregistered for meeting',
            'meeting'   =>  $meeting,
            'user'  =>  'tbd',
            'register'  =>  [
                'href'  => 'api/v1/meeting/registration',
                'method'    =>  'POST',
                'params'    =>  'user_id, meeting_id'
            ]
        ];

        return response()->json($response, 200);
    }
}
