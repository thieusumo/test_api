<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\TaskDetail;
use App\User;
use Illuminate\Http\Request;

class ReportController extends Controller {

	public function search(Request $request) {
		try {
			$data = $request->all();

			$tasks = TaskDetail::with(['task', 'logs', 'extend']);

			if ($data['status'] == 'all') {} else {
				if ($data['status'] == '3') {
					$tasks = $tasks->whereDate('due', '>', today());
				} else {
					$tasks = $tasks->where('status', $data['status']);
				}
			}
			if ($data['name'] != "") {
				$name = "%" . str_replace(" ", "%", $data['name']) . "%";
				$tasks = $tasks->whereHas('task', function ($query) use ($name) {
					$query->where('name', 'like', $name);
				});
			}
			if ($data['from'] != "" && $data['to'] != "") {
				$tasks = $tasks->whereHas('task', function ($query) use ($data) {
					$query->whereBetween('date_start', [$data['from'], $data['to']]);
				});
			}
			if ($data['id_user'] == "all") {} elseif ($data['id_user'] != "all" && $data['id_user'] != "") {
				$tasks = $tasks->where('user_id', $data['id_user']);
			}
			if ($data['id_project'] != "") {
				$tasks = $tasks->whereHas('task', function ($query) use ($data) {
					$query->where('id_project', $data['id_project']);
				});
			}

			$tasks = $tasks->latest()->get();

			return response()->json($tasks);

		} catch (\Exception $e) {
			\Log::info($e);
			return response()->json(['status' => 'error', 'message' => 'Get Tasks Failed!']);
		}
	}
	public function getUser(Request $request) {
		try {
			$users = User::all();
			$projects = Project::latest()->get();
			return response()->json(['users' => $users, 'projects' => $projects]);
		} catch (\Exception $e) {
			\Log::info($e);
			return response()->json(['status' => 'error', 'message' => 'Get Users Failed!']);
		}
	}
}
