<?php

namespace App\Http\Controllers;

use App\Models\Customer as CustomerModel;
use App\Models\Test;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class Customer extends Controller
{
    public function create(Request $request){
        $validator=Validator::make($request->all(),[
           'customer_id'=>'required',
            'test_types_id'=>'required',
            'test_center_id'=>'required',
        ]);
        if($validator->fails()){
            return Response::json(['status'=>'error','message'=>'Required information missing']);
        }
        $test_time=new Carbon();
        if($request->input('test_time')){
            $test_time=$request->input('test_time');
        }
        $validatedData=$validator->validated();
        $validatedData['test_time']=$test_time;
            $test=new Test($validatedData);
        try {
            $test->save();
            return Response::json(['status'=>'success','message'=>'Inserted Successfully','test'=>$test]);
        }catch (\Exception $ex){
            return Response::json(['status'=>'error','message'=>'Failed to create data']);
        }

    }
    public function delete(Request $request,$id){
        $test=Test::destroy($id);
        if($test){
            return Response::json(['status'=>'success','message'=>'Successfully Deleted']);
        }
        return Response::json(['status'=>'error','message'=>'Failed to delete data']);
    }
    public function get(Request $request,$id){
        $test=Test::find($id);
        if($test){
            return Response::json(['status'=>'success','result'=>$test]);
        }
        return Response::json(['status'=>'error','message'=>'Record not found!']);
    }
    public function getTestsForTestCenter(Request $request){
        $validator=Validator::make($request->all(),[
            'test_center_id'=>'required'
        ]);
        if($validator->fails()){
            return Response::json(['status'=>'error','message'=>'Required information missing!']);
        }
        $test=Test::where('test_center_id',$request->input('test_center_id'))->where(function ($query) use ($request){
            return $query->where('result',$request->input('result'));
        })->get();
        if($test){
            return Response::json(['status'=>'success','result'=>$test]);
        }
        return Response::json(['status'=>'error','message'=>'Record not found!']);
    }
    public function checkin(Request $request){
        $validator=Validator::make($request->all(),[
           'first_name'=>'required',
           'last_name'=>'required',
           'email'=>'required',
           'birthday'=>'required',
           'street'=>'required',
           'nr'=>'required',
           'zip'=>'required',
           'city'=>'required',
           'number'=>'required',
           'TestcenterId'=>'required',
           'TesttypeId'=>'required',
        ]);
        $customer=CustomerModel::where('email',$request->input('email'))->first();
        if($validator->fails()){
            return Response::json(['status'=>'error','message'=>'Required information missing!']);
        }
        if($customer){
            return Response::json(['status'=>'error','message'=>'Email Already Exists']);
        }else{
            $customer=new CustomerModel($validator->safe()->except([
                'TestcenterId',
                'TesttypeId',
                'password'
            ])->validated());
            $customer->password=md5($request->input('password'));
            $customer->terms=1;
            try {
                $customer->save();
            }catch (\Exception $ex){
                return Response::json(['status'=>'error','message'=>'Failed to create customer. ']);
            }
            $test=new Test([
                'customer'=>$customer->id,
                'test_types_id'=>$request->input('TesttypeId'),
                'test_center_id'=>$request->input('TestcenterId'),
                'test_time'=>new Carbon()
            ]);
            try {
                $test->save();
                return Response::json(['status'=>'success','message'=>'Test Created Successfully']);

            }catch (\Exception $ex){
                return Response::json(['status'=>'error','message'=>'Invalid Data provided for tests']);
            }
        }
    }
}
