<?php

namespace Amp;

class WorkerThread extends \Worker {

    private $sharedData;
    private $ipcSocket;

    function __construct(SharedData $sharedData, $ipcSocket, $bootstrapPath = NULL) {
        $this->sharedData = $sharedData;
        $this->ipcSocket = $ipcSocket;
        if ($bootstrapPath) {
            require_once $bootstrapPath;
        }
    }

    function fufill($data, $failed) {
        $this->sharedData[] = $data;
        $notification = $failed ? "-" : "+";
        fwrite($this->ipcSocket, $notification);
    }

    function run() {
        //register_shutdown_function([&$this, 'onShutdown']);
    }

    function onShutdown() {
        if (!($this->getStacked() || $this->isWorking())) {
            return;
        }

        $fatals = [E_ERROR, E_PARSE, E_USER_ERROR, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING];
        $lastError = error_get_last();

        if ($lastError && in_array($lastError['type'], $fatals)) {
            extract($lastError);
            $errorMsg = sprintf("%s in %s on line %d", $message, $file, $line);
            fwrite($this->ipcSocket, "x");
        }
    }
}