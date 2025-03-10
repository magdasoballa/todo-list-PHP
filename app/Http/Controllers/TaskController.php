<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            $tasks = Task::with('comments')->get();
        } else {
            $tasks = $user->tasks()->with('comments')->get();
        }

        return Inertia::render('Tasks/Index', [
            'tasks' => $tasks
        ]);
    }


    public function store(Request $request)
    {
        if (Auth::check()) {
            $request->validate([
                'title' => 'required|string|max:255',
            ]);

            Auth::user()->tasks()->create([
                'title' => $request->title,
            ]);

            return redirect()->route('tasks.index');
        } else {
            return redirect()->route('login');
        }
    }

    public function update(Request $request, Task $task)

    {
        $user = Auth::user();

        if ($user->role !== 'admin' && $task->user_id !== $user->id) {
            abort(403);
        }

        $task->title = $request->title;
        $task->save();

        return redirect()->route('tasks.index');
    }

    public function destroy(Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            abort(403);
        }

        $task->comments()->delete();
        $task->delete();


        return redirect()->route('tasks.index');
    }

    public function toggleCompletion(Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            abort(403);
        }

        $task->completed = !$task->completed;
        $task->save();

        return redirect()->route('tasks.index');
    }
}
