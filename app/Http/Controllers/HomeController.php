<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $files = scandir(public_path('logs'));
        $logFiles = array_values(array_diff($files, ['.', '..']));

        $logs = [];
        foreach ($logFiles as $logFile) {
            $logs[] = [
                'name' => $logFile,
                'path' => asset('logs/' . $logFile),
                'selected' => false
            ];
        }

        if (count($logs) > 0) {
            $logs[count($logs) - 1]['selected'] = true;
        }

        return view('pages.logs-viewer', compact('logs'));

        // return view('welcome');
    }
}
