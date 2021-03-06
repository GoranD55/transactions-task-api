<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexTransactionsRequest;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\SubmitTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Services\TransactionsService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionsController extends Controller
{
    private TransactionsService $transactionsService;

    public function __construct(TransactionsService $transactionsService)
    {
        $this->transactionsService = $transactionsService;
    }

    public function index(IndexTransactionsRequest $request): JsonResource
    {
        $requestData = $request->validated();
        return TransactionResource::collection(
            $this->transactionsService->index($requestData['user_id'])
        );
    }

    public function show(string $transaction_id): TransactionResource | JsonResponse
    {
        $transaction = $this->transactionsService->show($transaction_id);
        if (!$transaction) {
            return response()->json([
                'status' => false,
                'reason' => 'Transaction not found!'
            ], 404);
        }

        return new TransactionResource($transaction);
    }

    public function store(StoreTransactionRequest $request): TransactionResource | JsonResponse
    {
        try {
            $transaction = $this->transactionsService->store($request->validated());

            return new TransactionResource($transaction);
        } catch (Exception $exception) {
            return response()->json([
                'status' => false,
                'reason' => $exception->getMessage(),
            ], 400);
        }
    }

    public function submit(SubmitTransactionRequest $request): JsonResponse
    {
        try {
            $requestData = $request->validated();
            $this->transactionsService->submit($requestData['transaction_id']);
        } catch (Exception $exception) {
            return response()->json([
                'status' => false,
                'reason' => $exception->getMessage(),
            ], 400);
        }

        return response()->json(['message' => 'Successful!']);
    }
}
