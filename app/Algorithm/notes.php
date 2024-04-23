<?php
namespace App\Algorithm;

/**
 * ********** interaction between use cases and algorithm 
 * question 
 * * delete question: delete combination and useing, in question controller
 * * accept question: create combination of question
 * 
 * question choice (if question status is accepted)
 * * add : re create combination of question 
 * * modify : re create combination of question 
 * * delete : re create combination of question 
 * 
 * lecturer online exam 
 * * add : create form of exam, update question usage 
 * * delete: update question usage
 * 
 * student online exam 
 * * saveOnlineExamQuestionAnswer: update question usage 
 * 
 * paper exam 
 * * add : create form of exam, update question usage 
 * * delete: update question usage
 * * export: 
 * * يتم النظر متى يتم تحديث بيانات استخدام السؤال هل عند الاضافة او التصدير
 * 
 * practise exam 
 * * add : create exam, update question usage 
 * * delete: update question usage
 * * savePractiseExamQuestionAnswer: update question usage
 * 
 * 
 */

/**
 * ************** algorithm structure  
 * ***** use cases 
 * generateQuestionChoicesCombination (questionId):{}
 * regenerateQuestionChoicesCombination (questionId):{}
 * generateOnlineExam (exam data, topicsIds ) : {}
 * generate paper exam (exam data, topicsIds ) : {}
 * update student question usage answer (questionId, ????????) : {}
 * update quest question usage answer (questionId, ????????) : {}
 * 
 * ***** Processes 
 * 
 * ***** models  
 * exam data: 
 * 
 */

 /**
  * exam data
  *     exam (duration, language id, difficulty level id
  *     form (count, configuration method id)
  *     question type (id, questions count)
  * 
  */