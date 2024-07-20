<?php

namespace App\AlgorithmAPI;

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
        try {
            //code...
            $jsonData = json_encode($data);
            $methodName = 'generate';

            $process = new Process([
                // 'E:\Applications\Python\Python312\python.exe',
                 // 'C:\Users\dell\AppData\Local\Programs\Python\Python312\python.exe',
                 'C:\Users\Nasser\AppData\Local\Programs\Python\Python39\python.exe',
                base_path() . '\app\AlgorithmAPI\PythonModules\examGeneratorAPI\start.py',
                $methodName
            ]);

            $process->setEnv([
                'SYSTEMROOT' => getenv('SYSTEMROOT'),
                'PATH' => getenv('PATH')
            ]);

            $process->setInput($jsonData); // Pass the JSON data via stdin

            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            // $updatedArray = json_decode($process->getOutput(), true);
            $resultData = json_decode($process->getOutput());
            return $resultData;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
