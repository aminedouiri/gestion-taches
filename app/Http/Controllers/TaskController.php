<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\TaskResource;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ListTaskRequest;
use App\Http\Requests\CreateTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskController extends Controller
{
    public function store(CreateTaskRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();
            if($user->hasRole('Utilisateur') && $user->id != $request->user_id) {
                Log::error('You are not authorized to create a task for another user');
                return $this->sendErrorResponse('You are not authorized to create a task for another user');
            }
            $task = Task::create($request->validated());
            DB::commit();
            Log::info('Task registered successfully.', ['task' => $task->titre]);
            return $this->sendSuccessResponse($task, 'Task registered successfully!', 201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Task registration failed.', ['error' => $e->getMessage()]);
            return $this->sendErrorResponse($e->getMessage());
        }
    }

    public function index(ListTaskRequest $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);
            $tasks = Task::whereNull('deleted_at');
            $user = Auth::user();
            if($user->hasRole('Administrateur')) {
                $tasks = $tasks;
            }
            else if($user->hasRole('Utilisateur')) {
                $tasks = $tasks->where('user_id', $user->id);
            }
            $totalTasks = $tasks->count();
            $paginator = $tasks->paginate($perPage, ['*'], 'page', $page);
            $currentPage = $paginator->currentPage();
            $totalPages = $paginator->lastPage();
            DB::commit();
            Log::info('Task list retrieved successfully.');
            return $this->sendSuccessResponse([
                'tasks' => TaskResource::collection($paginator),
                'current_page' => $currentPage,
                'total_pages' => $totalPages,
            ], 'Task list retrieved successfully');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Task list retrieved failed.', ['error' => $e->getMessage()]);
            return $this->sendErrorResponse($e->getMessage());
        }
    }

    public function show(int $id)
    {
        try {
            $user = Auth::user();
            $task = Task::find($id);
            if(!isset($task)) {
                DB::commit();
                Log::warning('Task not found');
                return $this->sendErrorResponse('Task not found', 404);
            }
            if($user->hasRole('Utilisateur') && $task->user_id != $user->id) {
                Log::warning('Task not found');
                return $this->sendErrorResponse('Task not found', 404);
            }
            Log::info('Task retreived successfully.', ['task' => $task]);
            return $this->sendSuccessResponse(new TaskResource($task), 'Task retreived successfully!');
        } catch (Exception $e) {
            Log::error('Task registration failed.', ['error' => $e->getMessage()]);
            return $this->sendErrorResponse($e->getMessage());
        }
    }

    public function update(UpdateTaskRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $task = Task::find($id);
            $data = $request->validated();
            $user = Auth::user();
            if(!isset($task)) {
                DB::commit();
                Log::warning('Task not found');
                return $this->sendErrorResponse('Task not found', 404);
            }
            if($user->hasRole('Utilisateur') && $task->user_id != $user->id) {
                DB::commit();
                Log::warning('Task not found');
                return $this->sendErrorResponse('Task not found', 404);
            }
            if($user->hasRole('Utilisateur')) {
                $data = $request->validated();
                unset($data['user_id']);
            }
            $task->update($data);
            DB::commit();
            Log::info('Task updated successfully.', ['task' => $task->titre]);
            return $this->sendSuccessResponse($task, 'User created successfully!', 201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Task updated failed.', ['error' => $e->getMessage()]);
            return $this->sendErrorResponse($e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $user = Auth::user();
            $task = Task::find($id);
            if($user->hasRole('Utilisateur') && isset($task)) {
                if($task->user_id !== $user->id) {
                    DB::commit();
                    Log::warning('Task not found');
                    return $this->sendErrorResponse('Task not found', 404);
                }
            }
            if(!isset($task)) {
                DB::commit();
                Log::warning('Task not found');
                return $this->sendErrorResponse('Task not found', 404);
            }
            $task->delete();
            DB::commit();
            Log::info('Task deleted successfully.', ['task' => $task->titre]);
            return $this->sendSuccessResponse($task, 'Task deleted successfully!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Task deleted failed.', ['error' => $e->getMessage()]);
            return $this->sendErrorResponse($e->getMessage());
        }
    }

    public function deleted(ListTaskRequest $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);
            $tasks = Task::onlyTrashed();
            DB::commit();
            Log::info('Task deleted list retreived successfully.');
            $paginator = $tasks->paginate($perPage, ['*'], 'page', $page);
            $currentPage = $paginator->currentPage();
            $totalPages = $paginator->lastPage();
            return $this->sendSuccessResponse([
                'tasks' => TaskResource::collection($paginator),
                'current_page' => $currentPage,
                'total_pages' => $totalPages,
            ], 'Task list retrieved successfully');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Task deleted list retreived failed.', ['error' => $e->getMessage()]);
            return $this->sendErrorResponse($e->getMessage());
        }
    }
}
