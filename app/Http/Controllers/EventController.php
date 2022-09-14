<?php

namespace App\Http\Controllers;
use App\Models\Event;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    // this function for find the overlap events | if found any overlap event by giving start and end date time, function will return true otherwise else
    public function OverlapChecker($start, $end)
    {
        $events = Event::where('user_id', '=', auth('sanctum')->user()->user_id)->get();
        $overlaper = false;
        foreach ($events as $event){
            if ($end > $event->start_at){
                if ($start > $event->end_at){
                    $overlaper = false;
                }else{
                    $overlaper = true;
                }
            }elseif($event->end_at > $start){
                if ($event->start_at > $end){
                    $overlaper = false;
                }
                $overlaper = true;
            }
        }
        return $overlaper;
    }

    //    this function for add events to calendar by passing event name, start and end date time and optionally accepted the description
    public function addEvent(Request $request)
    {
        $validator = validator::make($request->all(), [
            'event' => 'required',
            'start_at' => 'required',
            'end_at' => 'required',
        ]);
        if ($validator->fails()) {
            if ($validator->errors()->has('event')) {
                return response([
                    'status' => 400,
                    'message' => 'Event name is required'
                ]);
            }
            if ($validator->errors()->has('start_at')) {
                return response([
                    'status' => 400,
                    'message' => 'Event starting date and time is required'
                ]);
            }
            if ($validator->errors()->has('end_at')) {
                return response([
                    'status' => 400,
                    'message' => 'Event ending date and time is required'
                ]);
            }
        }
        $validated = $validator->validated();
        if ($this->OverlapChecker($validated['start_at'], $validated['end_at'])){
            return response([
                'status' => 400,
                'message' => 'These time range have any other Events'
            ]);
        }
        try{
            $event = new Event();
            $event->user_id = auth('sanctum')->user()->user_id;
            $event_final = User::where('user_id', '=', auth('sanctum')->user()->user_id)->orderBy('user_id', 'desc')->first();
            $event->event_id = $event_final->event_id != null?$event_final->event_id+1:1;
            $event->start_at = $validated['start_at'];
            $event->end_at = $validated['end_at'];
            $event->event = $validated['event'];
            $event->description = isset($request->description)?$request->description:null;
            $event->save();
            return response([
                'status' => 200,
                'message' => 'Event added successfully',
                'event id' => $event->event_id ,
            ]);
        }catch (Exception $e){
            return response([
                'status' => 400,
                'message' => 'Something went wrong...!',
                'error' => $e->getMessage(),
            ]);
        }

    }

    //    this function for update existing events in calendar by passing relavent details.
    public function updateEvent(Request $request,$event_id)
    {
        if (!isset($event_id)){
            return response([
                'status' => 400,
                'message' => 'Event id is required'
            ]);
        }
        $event = Event::where('event_id', '=', $event_id)->where('user_id', '=', auth('sanctum')->user()->user_id)->first();
        if ($event == null){
            return response([
                'status' => 400,
                'message' => 'Event can not find'
            ]);
        }
        if (isset($request->start_at) || isset($request->end_at)){
            if (isset($request->start_at) || !isset($request->end_at)){
                $overlapper_result =  $this->OverlapChecker($request->start_at, $event->end_at);
            }elseif (!isset($request->start_at) || isset($request->end_at)){
                $overlapper_result =  $this->OverlapChecker($event->start_at,$request->end_at);
            }else
            {
                $overlapper_result =  $this->OverlapChecker($request->start_at,$request->end_at);
            }
            if ($overlapper_result){
                return response([
                    'status' => 400,
                    'message' => 'These time range have any other events'
                ]);
            }
        }
        try{
            $event->start_at =isset($request->start_at)?$request->start_at:$event->start_at;
            $event->end_at =isset($request->end_at)?$request->end_at:$event->end_at;
            $event->event =isset($request->event)?$request->event:$event->event;
            $event->description =isset($request->description)?$request->description:$event->description;
            $event->save();
            return response([
                'status' => 200,
                'message' => 'Event updated successfully',
                'event id' => $event->event_id ,
            ]);
        }catch (Exception $e){
            return response([
                'status' => 400,
                'message' => 'Something went wrong...!',
                'error' => $e->getMessage(),
            ]);
        }
    }
    //    this function for delete the user event by apassing user id
    public function deleteEvent($event_id)
    {
        if (!isset($event_id)){
            return response([
                'status' => 400,
                'message' => 'Event id is required'
            ]);
        }
        $event = Event::where('event_id', '=', $event_id)->where('user_id', '=', auth('sanctum')->user()->user_id)->get();
        if ($event == null){
            return response([
                'status' => 400,
                'message' => 'Event can not find'
            ]);
        }
        try{
            $event->delete();
            return response([
                'status' => 200,
                'message' => 'Event deleted successfully',
                'event id' => $event->event_id ,
            ]);
        }catch (Exception $e){
            return response([
                'status' => 400,
                'message' => 'Something went wrong...!',
                'error' => $e->getMessage(),
            ]);
        }

    }

    // this functions for view required events details
    public function viewEvent($event_id)
    {
        if (!isset($event_id)){
            return response([
                'status' => 400,
                'message' => 'Event id is required'
            ]);
        }
        $event = Event::where('event_id', '=', $event_id)->where('user_id', '=', auth('sanctum')->user()->user_id)->first();
        if ($event == null){
            return response([
                'status' => 400,
                'message' => 'Event can not find'
            ]);
        }
        try{
            return response([
                'status' => 200,
                'event' => $event,
                'event id' => $event->event_id ,
            ]);
        }catch (Exception $e){
            return response([
                'status' => 400,
                'message' => 'Something went wrong...!',
                'error' => $e->getMessage(),
            ]);
        }

    }

    // this function for view all the events of logged user
    public function viewEvents()
    {
        $events = Event::where('user_id', '=', auth('sanctum')->user()->user_id)->get();
        if ($events == null){
            return response([
                'status' => 200,
                'message' => 'You have not any events'
            ]);
        }
        try{
            return response([
                'status' => 200,
                'event' => $events,
            ]);
        }catch (Exception $e){
            return response([
                'status' => 400,
                'message' => 'Something went wrong...!',
                'error' => $e->getMessage(),
            ]);
        }

    }
}
