<?php

namespace App\Http\Controllers;

use App\Models\OnlineExam;
use Illuminate\Http\Request;

class OnlineExamController extends Controller
{
    // retrieve online exams (part id?, type id?, status id?) :
    //{ [id, course name, course part name, datetime, type name, status name, language name] }
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(OnlineExam $onlineExamDetails)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OnlineExam $onlineExamDetails)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OnlineExam $onlineExamDetails)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OnlineExam $onlineExamDetails)
    {
        //
    }

    public function retrieveExams(Request $request)
    {
        // Retrieve the parameters from the request
        $partId = $request->input('part_id');
        $typeId = $request->input('type_id');
        $statusId = $request->input('status_id');

        // Query to retrieve online exams with specific criteria
        $exams = OnlineExam::with(['course', 'part', 'type', 'status', 'language'])
            ->when($partId, function ($query) use ($partId) {
                return $query->where('part_id', $partId);
            })
            ->when($typeId, function ($query) use ($typeId) {
                return $query->where('type_id', $typeId);
            })
            ->when($statusId, function ($query) use ($statusId) {
                return $query->where('status_id', $statusId);
            })
            ->select('id', 'course_name', 'course_part_name', 'datetime', 'type_name', 'status_name', 'language_name')
            ->get();

        // Return the result as a response
        return response()->json($exams);
    }


    public function getExams(Request $request)
    {
        $query = OnlineExam::with([
            'course' => function ($query) {
                $query->select('id', 'name');
            },
            'coursePart' => function ($query) {
                $query->select('id', 'name');
            },
            'type' => function ($query) {
                $query->select('id', 'name');
            },
            'status' => function ($query) {
                $query->select('id', 'name');
            },
            'language' => function ($query) {
                $query->select('id', 'name');
            },
        ]);

        // Apply optional filters based on request parameters
        $partId = $request->get('part_id');
        $typeId = $request->get('type_id');
        $statusId = $request->get('status_id');

        if ($partId) {
            $query->where('course_part_id', $partId);
        }

        if ($typeId) {
            $query->where('type_id', $typeId);
        }

        if ($statusId) {
            $query->where('status_id', $statusId);
        }

        // Fetch the results and return the desired response format
        $exams = $query->get();

        // Extract desired data from relationships
        $formattedExams = $exams->map(function ($exam) {
            return [
                'id' => $exam->id,
                'course_name' => $exam->course->name,
                'course_part_name' => $exam->coursePart->name,
                'datetime' => $exam->datetime,
                'type_name' => $exam->type->name,
                'status_name' => $exam->status->name,
                'language_name' => $exam->language->name,
            ];
        });

        return response()->json([
            'data' => $formattedExams,
        ]);
    }
}
