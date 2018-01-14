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

    protected function getSuccessfulDeleteMessage(string $resource = ''): string
    {
        return ! empty($resource) ? ucfirst($resource) . ' deleted successfully.' : 'Deleted successfully.';
    }

    protected function getFailedDeleteMessage(string $resource = ''): string
    {
        return ! empty($resource) ? ucfirst($resource) . ' deleted failed.' : 'Deleted failed.';
    }
}
