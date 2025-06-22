<?php

namespace App\Observers;

use App\Models\Webhook;
use Illuminate\Support\Facades\Log;

class WebhookObserver
{
    public function created($model)
    {
        $this->fireWebhook($model, 'create');
    }

    public function updated($model)
    {
        $this->fireWebhook($model, 'update');
    }

    public function deleted($model)
    {
        $this->fireWebhook($model, 'delete');
    }

    protected function fireWebhook($model, $action)
    {
        $modelType = strtolower(class_basename($model));
        $event = "{$modelType}.{$action}";
        Log::info("WebhookObserver: Firing event", ['event' => $event, 'model' => $model]);

        // Only fire for allowed actions
        if (in_array($event, [
            'project.create', 'project.update', 'project.delete',
            'task.create', 'task.update', 'task.delete',
            'user.create', 'user.update', 'user.delete',
        ])) {
            Webhook::execute($event, $model->toArray());
        }
    }
}