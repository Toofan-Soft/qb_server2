<?php

namespace App\AlgorithmAPI;

use App\Enums\QuestionStatusEnum;
use Illuminate\Support\Facades\DB;
use App\Enums\AccessibilityStatusEnum;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class GenerateExam
{
    /**
     * اذا كان لا يوجد اختلاف في اسئلة النماذج يتم ارجاع  البيانات كتالي....
     *
     */
    public function execute($data)
    {
        $jsonData = json_encode($data);
        $methodName = 'generate';
        $process = new Process([
            'E:\Applications\Python\Python312\python.exe',
            base_path() . '\app\AlgorithmAPI\PythonModules\examGeneratorAPI\start.py',
            $methodName,
            $jsonData
        ]);

        $process->setEnv([
            'SYSTEMROOT' => getenv('SYSTEMROOT'),
            'PATH' => getenv('PATH')
        ]);

        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // $updatedArray = json_decode($process->getOutput(), true);
        $resultData = json_decode($process->getOutput());
        return $resultData;
    }
}

