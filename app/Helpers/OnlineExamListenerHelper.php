<?php

namespace App\Helpers;

use App\Models\OnlineExam;
use App\Models\StudentAnswer;
use App\Models\StudentOnlineExam;
use App\Helpers\ProcessDataHelper;
use App\Events\ProctorRefreshEvevnt;
use App\Events\StudentRefreshEvevnt;
use App\Enums\StudentOnlineExamStatusEnum;

class OnlineExamListenerHelper
{
    // public static function refreshProctor($uid, $student_id) {
    // public static function refreshProctor($student_id, $exam_id, $form_id=null) {
    //     $exam = StudentOnlineExam::where('online_exam_id', $exam_id)
    //         // ->where('form_id', $form_id)
    //         ->first();
        
    //     // $data = [];
    //     $data['id'] = $student_id;

    //     if ($exam) {

    //         $data['status_name'] = $exam->status;
    //         $data['start_datetime'] = $exam->start_datetime;
    //         $data['end_datetime'] = $exam->end_datetime;

    //         if (intval($exam->status) === StudentOnlineExamStatusEnum::ACTIVE->value) {
    //             if ($exam->end_datetime === null) {
    //                 if ($exam->start_datetime !== null) {
    //                     $data['is_started'] = true;
    //                     // $item->is_finished = false;
    //                     // $item->is_suspended = false;
    //                 }
    //             } else {
    //                 // handle error...
    //             }
    //         } elseif (intval($exam->status) === StudentOnlineExamStatusEnum::SUSPENDED->value) {
    //             if ($exam->end_datetime === null) {
    //                 if ($exam->start_datetime !== null) {
    //                     $data['is_started'] = true;
    //                     // $item->is_finished = false;
    //                     $data['is_suspended'] = true;
    //                 }
    //             } else {
    //                 // handle error...
    //             }
    //         } elseif (intval($exam->status) === StudentOnlineExamStatusEnum::COMPLETE) {
    //             if ($exam->start_datetime !== null || $exam->end_datetime !== null) {
    //                 $data['is_started'] = true;
    //                 $data['is_finished'] = true;
    //                 // $item->is_suspended = false;
    //             } else {
    //                 // handle error...
    //             }
    //         } else {
    //             // handle error...
    //         }

    //         // $item->answered_questions_count = StudentAnswer::where('student_id', $item->id)
    //         //     ->where('form_id', $exam->form_id)->count();

    //         $data = ProcessDataHelper::enumsConvertIdToName($data, [new EnumReplacement('status_name', StudentOnlineExamStatusEnum::class)]);
    //     }

    //     event(new ProctorRefreshEvevnt($data));
    // }



    public static function refreshProctor($student_id, $exam_id, $form_id=null) {        
        $uid = OnlineExam::findOrFail($exam_id)->employee()->user()->id;

        $exam = StudentOnlineExam::where('online_exam_id', $exam_id)
            // ->where('form_id', $form_id)
            ->first();
        
        // $data = [];
        $data['id'] = $student_id;
        $data['form_name'] = $form_id;

        if ($exam) {

            $data['status_name'] = $exam->status;
            $data['start_time'] = $exam->start_datetime;
            $data['end_time'] = $exam->end_datetime;

            if (intval($exam->status) === StudentOnlineExamStatusEnum::ACTIVE->value) {
                if ($exam->end_datetime === null) {
                    if ($exam->start_datetime !== null) {
                        $data['is_started'] = true;
                        // $item->is_finished = false;
                        // $item->is_suspended = false;
                    }
                } else {
                    // handle error...
                }
            } elseif (intval($exam->status) === StudentOnlineExamStatusEnum::SUSPENDED->value) {
                if ($exam->end_datetime === null) {
                    if ($exam->start_datetime !== null) {
                        $data['is_started'] = true;
                        // $item->is_finished = false;
                        $data['is_suspended'] = true;
                    }
                } else {
                    // handle error...
                }
            } elseif (intval($exam->status) === StudentOnlineExamStatusEnum::COMPLETE) {
                if ($exam->start_datetime !== null || $exam->end_datetime !== null) {
                    $data['is_started'] = true;
                    $data['is_finished'] = true;
                    // $item->is_suspended = false;
                } else {
                    // handle error...
                }
            } else {
                // handle error...
            }

            $data['answered_questions_count'] = StudentAnswer::where('student_id', $student_id)
                ->where('form_id', $exam->form_id)->count();

            $data = ProcessDataHelper::enumsConvertIdToName($data, [new EnumReplacement('status_name', StudentOnlineExamStatusEnum::class)]);
        }

        event(new ProctorRefreshEvevnt($data, $uid));
    }

    public static function refreshStudent($student_id, $exam_id, $form_id=null) {        
        $uid = OnlineExam::findOrFail($exam_id)->employee()->user()->id;
        
        $exam = StudentOnlineExam::where('online_exam_id', $exam_id)
            // ->where('form_id', $form_id)
            ->first();

        $uid = $exam->student()->user()->id;
        
        // $data = [];

        if ($exam) {

            $data['status_name'] = $exam->status;
            $data['start_datetime'] = $exam->start_datetime;
            $data['end_datetime'] = $exam->end_datetime;

            if (intval($exam->status) === StudentOnlineExamStatusEnum::ACTIVE->value) {
                $data['is_takable'] = true;
            } elseif (intval($exam->status) === StudentOnlineExamStatusEnum::SUSPENDED->value) {
                $data['is_suspended'] = true;
            } elseif (intval($exam->status) === StudentOnlineExamStatusEnum::COMPLETE) {
                $data['is_complete'] = true;
            } elseif (intval($exam->status) === StudentOnlineExamStatusEnum::CANCELED) {
                $data['is_canceled'] = true;
            } else {
                // handle error...
            }
        }

        event(new StudentRefreshEvevnt($data, $uid));
    }

    public static function refreshAll($student_id, $exam_id, $form_id=null) {
        // when exam time finish, then refresh proctor and all students...
    }
}
