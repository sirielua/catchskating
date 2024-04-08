<?php

namespace App\MessageBus;

use Illuminate\Support\Facades\DB;

class TransactionBus extends MessageBusDecorator
{
    private $afterCommitCallback;
    
    public function __construct(MessageBusInterface $decorated, callable $afterCommitCallback = null)
    {
        $this->afterCommitCallback = $afterCommitCallback;
        parent::__construct($decorated);
    }
    
    #[\Override]
    public function dispatch($message)
    {
        $alreadyStarted = (bool)DB::transactionLevel();
        
        if (false === $alreadyStarted) {
            DB::beginTransaction();
        }
        
        try {
            $result = $this->bus->dispatch($message);
            
            if (false === $alreadyStarted) {
                DB::commit();
                $this->afterCommit();
            }
            
            return $result;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    private function afterCommit()
    {
        if (is_callable($this->afterCommitCallback)) {
            call_user_func($this->afterCommitCallback);
        }
    }
}
