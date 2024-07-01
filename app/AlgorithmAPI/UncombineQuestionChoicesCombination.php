<?php

namespace App\AlgorithmAPI;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class UncombineQuestionChoicesCombination
{
    public function execute($data)
    {

    $jsonData = json_encode($data);
    $methodName = 'uncombine';
    $process = new Process([
        'E:\Applications\Python\Python312\python.exe',
        base_path() . '\app\AlgorithmAPI\PythonModules\combinationGeneratorAPI\start.py',
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
