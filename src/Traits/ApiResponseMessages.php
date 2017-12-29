<?php

namespace ReaDev\ApiHelpers\Traits;

trait ApiResponseMessages
{
    // TODO: Use translations instead of hard coded strings

    protected function getSuccessfulUpdateMessage(string $resource = ''): string
    {
        return ! empty($resource) ? ucfirst($resource) . ' updated successfully.' : 'Updated successfully.';
    }

    protected function getFailedUpdateMessage(string $resource = ''): string
    {
        return ! empty($resource) ? ucfirst($resource) . ' updated failed.' : 'Updated failed.';
    }
}
