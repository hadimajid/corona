<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Test;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class TestController extends Controller
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
            if($request->input('result')){
                return $query->where('result','like',$request->input('result'));
            }
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
            'password'=>'required',
            'birthday'=>'required',
            'street'=>'required',
            'nr'=>'required',
            'zip'=>'required',
            'city'=>'required',
            'number'=>'required',
            'TestcenterId'=>'required',
            'TesttypeId'=>'required',
        ]);
        if($validator->fails()){
            return Response::json(['status'=>'error','message'=>'Required information missing!']);
        }
        $customer=Customer::where('email',$request->input('email'))->first();
        if($customer){
            return Response::json(['status'=>'error','message'=>'Email Already Exists']);
        }else{
            $customer=new Customer($validator->safe()->except([
                'TestcenterId',
                'TesttypeId',
                'password'
            ]));
            $customer->password=md5($request->input('password'));
            $customer->terms=1;
            try {
                $customer->save();
            }catch (\Exception $ex){
                return Response::json(['status'=>'error','message'=>'Failed to create customer. ']);
            }
            $test=new Test([
                'customer_id'=>$customer->id,
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
    public function getResult(Request $request){
        $validator=Validator::make($request->all(),[
            'email'=>'required',
            'birthYear'=>'required',
        ]);
        if($validator->fails()){
            return Response::json(['status'=>'error','message'=>'Required information missing']);
        }
        $customer=Customer::where('email',"like",$request->input('email'))
            ->where(DB::raw("YEAR(birthday)"),$request->input('birthYear'))
            ->first();
        if($customer){
            $tests=$customer->tests;
//            dd($tests);
            if(empty($tests)){
                return Response::json(['status'=>'error','message'=>"No Tests Found"]);
            }
            return Response::json(['status'=>'success','result'=>$tests]);

//            return Response::json(['status'=>'success','result'=>$test]);
        }
        return Response::json(['status'=>'error','message'=>"Information doesn\'t match with any users."]);
    }
    public function getTestBetween(Request $request){
        $validator=Validator::make($request->all(),[
            'date1'=>'required',
            'date2'=>'required',
        ]);
        if($validator->fails()){
            return Response::json(['status'=>'error','message'=>'Required information missing']);
        }

            $tests=DB::table('tests')
                ->selectRaw("tests.id as Testnummer, tests.url_pdf as PDF, customers.id as customer_id ,customers.first_name, customers.last_name, customers.email, customers.birthday, customers.terms, customers.street, customers.nr, customers.zip, customers.city, tests.result , tests.test_time, testcenter.street_name")
                ->join('customers','tests.customer_id','=','customers.id')
                ->join('testtypes','tests.test_types_id','=','testtypes.id')
                ->join('testcenter','tests.test_center_id','=','testcenter.id')
                ->where('test_time','>=',$request->input('date1'))
                ->where('test_time','<=',$request->input('date2'))
                ->orderBy('tests.id','desc')
                ->get();
            if(count($tests)==0){
                return Response::json(['status'=>'error','message'=>"No tests found in the given range."]);
            }
        return Response::json(['status'=>'success','result'=>$tests]);

    }
}
