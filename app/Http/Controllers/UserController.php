<?php

namespace App\Http\Controllers;

use App\Mail\SendEmail;
use App\Models\User;
use App\Models\UserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function __construct() {
        $this->middleware('auth:api', ['except'=>['login']]);
    }

    public function createUser(Request $request) {
        $validator = Validator::make($request->all(), [
            'first_name'  => 'required',
            'last_name'   => 'required',
            'email' => 'required|email|unique:users'
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $userRequestObject = json_encode($request->all());
        return $this->createRequest( $userRequestObject, config('enums.request_types.create'));   

    }

    public function updateUser(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'email' => 'email|unique:users'
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::find($id);
        if(!$user) {
            return response()->json(['error'=>'No user found with the id: '.$id], 400);
        }

        if($user->role == 'admin') {
            return response()->json(['error'=>'You are not authorized to edit a fellow admin record'], 400);
        }

        $userObject = json_encode($user);
        $userRequestObject = json_encode($user->fill($request->all()));
        

        return $this->createRequest( $userRequestObject, config('enums.request_types.update'), $userObject);
    }

    public function deleteUser($id) {
        $user = User::find($id);
        if(!$user) {
            return response()->json(['error' => 'No request found with the specified id'], 400);
        }

        if($user->role == 'admin') {
            return response()->json(['error'=>'You are not authorized to delete a fellow admin record'], 400);
        }

        $userRequestObject = json_encode($user);
        return $this->createRequest($userRequestObject, config('enums.request_types.delete'));
    }

    public function login(Request $request) {
        $validator = Validator::make($request->all(), [
            'email'     => 'required|email',
            'password'  => 'required'
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        if(!$token = auth()->attempt($validator->validated())) {
            return response()->json(['error'=>'Invalid user name or password'], 400);
        }

        return $this->createToken($token);
    }

    public function logout() {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function getPendingRequest() {
        $user = auth()->user();

        $response = [];

        $userRequests = UserRequest::where('initiator_id', '!=', $user->id)->get();
        foreach ($userRequests as $userRequest) {
            $request = [];
            $request['id'] = $userRequest->id;
            $request['request_type'] = $userRequest->request_type;

            if($userRequest->request_type == config('enums.request_types.update')) {
                $request['current_user_object'] = json_decode($userRequest->user_object);
            }
            
            $request['user_object'] = json_decode($userRequest->request_object);
            array_push($response, (object) $request);
        }

        return response()->json([
            'message' => 'Requests waiting for your approval',
            'pending_requests'=> $response
        ]);        
    }

    public function approveOrRejectRequest(Request $request){
        $user = auth()->user();
        $validator = Validator::make($request->all(), [
            'id'  => 'required',
            'approve'   => 'required|boolean'
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $userRequest = UserRequest::find($request->id);
        if(!$userRequest) {
            return response()->json(['error' => 'No request found with the specified id'], 400);
        }

        if($userRequest->initiator_id == $user->id) {
            return response()->json(['error' => 'You can not approve or reject a request initiated by you'], 400);
        }

        if(!$request->approve) {
            $userRequest->delete();
            return response()->json([
                'message' => 'Request rejected successfully'
            ]);  
        }

        return $this->approveRequest($userRequest);


    }

    private function createRequest($userRequestObject, $requestType, $userObject = null) {
        
        $userRequest = new UserRequest();
        $userRequest->request_type = $requestType;
        $userRequest->request_object = $userRequestObject;
        if(isset($userObject)) $userRequest->user_object = $userObject;
        $userRequest->initiator_id = auth()->user()->id;

        if($userRequest->save()) {
            //Best practice is to use background job to send emails, but just to keep things simple
            $this->sendMails($userRequest);
        }

        return response()->json(['message' => 'request successfully submitted for approval']);
    }

    private function createToken($token) {
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth()->factory()->getTTL() * 864000,
            'user'         => auth()->user() 
        ]);
    }

    private function approveRequest(UserRequest $userRequest) {

        $jsonObject = json_decode($userRequest->request_object, true);
        $user = new User();
        $user->forceFill($jsonObject);

        switch ($userRequest->request_type) {
            case config('enums.request_types.create'):
              $user->password = Hash::make(config('enums.default.password'));
              $user->role = config('enums.default.role');
              if($user->save()) {
                $userRequest->delete();
              }
              break;
            case config('enums.request_types.update'):
                $userToUpdate = User::find($user->id);
                if($userToUpdate->update($jsonObject)) {
                    $userRequest->delete();
                  }
              break;
            case config('enums.request_types.delete'):
                $userTodelete = User::find($user->id);
                $userTodelete->delete();
                $userRequest->delete();
              break;
            default:
                return response()->json(['error' => 'Invalid request type'], 400);
          }

          return response()->json([
            'message' => 'request approved successfully',
          ]);  
    }

    private function sendMails($request) {
        //Best practice is using background job, but to keep things simple
        $users = User::where('role', 'admin')->where('id', '!=', $request->initiator_id)->get();
        foreach($users as $user){
            $mailData = [
                'name' => $user->first_name,
                'body' => 'There is a request with type '.$request->request_type .' awaiting your approval',
            ];
    
            Mail::to($user->email)->send(new SendEmail($mailData));
        }
        
    }
    
}
