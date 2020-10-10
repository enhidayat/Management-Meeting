<?php

namespace App\Http\Controllers;

use App\Meeting;
use Illuminate\Http\Request;

use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth as JWTAuth;

class MeetingController extends Controller
{
    // public function __construct()
    // {
    //     membuat agar akses mthod index dan show tidak harus menggunakan token
    //     $this->middleware('jwt.auth', [
    //         'except'    => ['index', 'show']
    //     ]);
    // }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // mengambil semua data yg ada pd db dg eloquent via model Meeting(select * from)
        $meetings = Meeting::all();
        // $token = JWTAuth::toUser('token');
        // $user = JWTAuth::toUser($token);
        // $user = $user->id;
        $token = JWTAuth::getToken();
        $user = JWTAuth::toUser($token);

        //membuat perulangan untuk dpt url enampilkan detail meeting
        foreach ($meetings as $meeting) {
            $meeting->view_meeting = [
                'href'  =>  'api/v1/meeting/' . $meeting->id, //ambil id meeting
                'method'    =>  'GET'
            ];
        }
        //membuat respons 
        $response = [
            'msg'   =>  'list of all Meetings',
            'meetings'  =>  $meetings, //mgunakan var meetings bukan $meeting  utk memanggil data2, u/ view meeting akan mengikuti oomatis
            // 'token'  => $token
            'user' => $user
        ];

        return response()->json($response, 201);
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {


        //membuat validasi
        $this->validate($request, [
            'title' =>  'required',
            'description' =>  'required',
            'time' =>  'required',
            'user_id' =>  'required'
        ]);


        //membuat variable utk menerima request dr clien
        $title = $request->input('title');
        $description = $request->input('description');
        $time = $request->input('time');
        $user_id = $request->input('user_id');


        //membuat objek utk save data
        $meeting = new Meeting([
            'time'          =>  $time,
            'title'         =>  $title,
            'description'   =>  $description

        ]);

        if ($meeting->save()) {
            //jk data meeting berhasil disimpan ke tabel meeting data akan diikat berdasarkan id user
            $meeting->users()->attach($user_id);
            //ATTACH digunakan untuk menyimpan data ke tabel pivot (mengisi db meeting_user berdasarkan user_id yg diinputkan melalui reques  dan meeting_id yg barusan di buat). intinya bikin data sekaligus di tabel meeting dan meeting_user 
            //stlh berhasil di save pada tbl meeting dan meeting_user akan menampilkan meeting detail berdasarkan meeting yg telah dibuat
            $meeting->view_meeting = [
                'href'  =>  'api/v1/meeting/' . $meeting->id,
                'method'    =>  'GET'
            ];
            //dan membuat pesan
            $message = [
                'msg'   =>  'Meeting created',
                'meeting'  => $meeting //data meeting
            ];
            return response()->json($message, 201);
        }

        //MEMBUAT RESPON
        $response   = [
            'msg'   =>  'Error during creation'
        ];

        return response()->json($response, 404);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //membuat var meeting yg memanggil kelas meeting dan objek with (memanggil data meeting dan user yg berelasi berdasarkan id yang dipilih ). ex. mencari data meeting dg id 1 beserta data users yg berelasi dg data meeting dg id 1(misal data meeting dg id 1 punya 9 user yg meregistrasi beserta id yg membuatnya mk akan tampil dlm respon [yg tampil bukan hanya detail data meeting tp data user jg])
        $meeting = Meeting::with('users')->where('id', $id)->firstOrFail();
        $meeting->view_meeting = [
            'href' => 'api/v1/meeting',
            'method' => 'GET'
        ];
        $response = [
            'msg'   =>  'Meeting information',
            'meeting'   =>  $meeting
        ];
        return response()->json($response, 200);
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //membuat validasi
        $this->validate($request, [
            'title' =>  'required',
            'description' =>  'required',
            'time' =>  'required',
            'user_id' =>  'required'
        ]);


        //membuat variable utk menerima request/INPUTAN dr clien
        $title = $request->input('title');
        $description = $request->input('description');
        $time = $request->input('time');
        $user_id = $request->input('user_id');
        //Mengambil data yang akan diedit berdasarkan id meeting dan id user
        $meeting = Meeting::with('users')->findOrFail($id); //findorfail = apabila dt tidak ada maka akan redirect kehalaman not found yg ud di handle

        //membuat kondisi apabila yg update bukan pembuat meeting maka akan gagal n ada notif
        //jk user != yd ada pada tavelpivot (meeting _user) saat pertama kali maka user tidak teregistrasi pd tbl meeting sbg pembuat meeting sehingga update akan dibatalkan
        if (!$meeting->users()->where('user_id', $user_id)->first()) {
            return response()->json(['msg' => 'user not registered for meeting, update not successfull'], 401);
        };
        //jk user sesuai (memang pembuat meeting yg melakukan update) lakukan proses

        //membuat var untuk inputan user /request
        $meeting->time = $time;
        $meeting->title = $title;
        $meeting->description = $description;

        //membuat kondisi jk berhasil / galat saat update
        if (!$meeting->update()) {
            return response()->json([
                'msg' =>    'Error during update'
            ], 404);
        }

        //jk berhasil update
        //akan diarahkan ke halaman detail
        $meeting->view_meeting = [
            'href' => 'api/v1/meeting' . $meeting->id,
            'method' => 'GET'
        ];

        //menampilkan data meeting dan notif
        $response = [
            'msg'   =>  'Meeting Update',
            'meeting'   =>  $meeting
        ];
        return response()->json($response, 200);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $meeting = Meeting::findOrFail($id); //memilih id yg akan dihapus
        //membuat var users utk mengambil dt2 user yg terkait disuatu meeting dg id yg dipilih
        $users  = $meeting->users;

        $meeting->users()->detach(); // methode detach untuk meghapus relasi tbl users dan meeting didalam tb pivot = meeting user) sebelum menghapus data meeting

        // jk delete gagal mk data user dinapus harus dikembalikan
        if (!$meeting->delete()) {
            foreach ($users as $user) {
                $meeting->users()->attach($user);
            }
            return response()->json([
                'msg'   =>  'Delete Failed'
            ], 404);
        }

        $response = [
            'msg'   =>  'Meeting deleted',
            'create'    => [
                'href'  =>  'api/v1/meeting',
                'method' =>  'POST',
                'params' =>  'title, description, time'
            ]
        ];
        return response()->json($response, 200);
    }
}
