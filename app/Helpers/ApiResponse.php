<?php 

namespace App\Helpers;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class ApiResponse{


    public static function success($data = [], string $message = 'successful', int $statusCode = 200, mixed $metadata = null)    {
        // Return response
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'metadata' => $metadata ? self::getMetadata($metadata) : null,
            // 'metadata' => $metadata ? self::generateMetadata($data) : null,
        ], $statusCode);
    }


    public static function error($error = null, string $message = 'there was an error', int $statusCode = 500)    {
        // Return response
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $error,
        ], $statusCode ?? 500);
    }

    // For getting metadata before loading the resource class
    private static function getMetadata($data){

        if ($data instanceof LengthAwarePaginator || $data instanceof Paginator) {
            return [
                'total' => $data->total(),
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'previous_page_url' => $data->previousPageUrl(),
                'next_page_url' => $data->nextPageUrl(),
                'pages' => $data->getUrlRange(1, $data->lastPage())
            ];
        }

        return null;
    }

    // For getting metadata without loading the resource class
    private static function generateMetadata($data){

        if ($data instanceof LengthAwarePaginator || $data instanceof Paginator) {
            return [
                'total' => $data->total(),
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'previous_page_url' => $data->previousPageUrl(),
                'next_page_url' => $data->nextPageUrl(),
                'pages' => $data->getUrlRange(1, $data->lastPage())
            ];
        }

        return null;
    }
    
}



// // Transform the items
// if($notes->count() >= 2){
//     $notes = NoteResource::collection($notes);
// }else{
//     $notes = new NoteResource($notes);
// }
// $metadata = $noteService->getMetadata($notes) ?? null;

// // Return response
// return $this->sendSuccess($notes, 'Notes fetched', 201, $metadata);

