<?php

namespace App\Http\Controllers;

use App\Mcp\Support\ImagePipeline;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * POST /upload — authenticated image upload for MCP clients.
 *
 * Send multipart field "file", or JSON {"image_base64": "...", "filename": "..."}.
 * Returns an upload_id valid for 15 minutes; pass it to any create/update tool
 * as image_upload_id. Header: Authorization: Bearer <MCP_TOKEN> (or ?token=).
 */
class McpUploadController
{
    public function store(Request $request): JsonResponse
    {
        // Housekeeping: drop expired uploads that were never attached.
        Media::whereNotNull('upload_token')->where('expires_at', '<', now())->delete();

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            if (! $file->isValid()) {
                return response()->json(['error' => 'The upload failed. Try again.'], 422);
            }
            $bytes = file_get_contents($file->getRealPath());
        } elseif ($b64 = $request->input('image_base64')) {
            if (str_starts_with($b64, 'data:')) {
                $b64 = substr($b64, strpos($b64, ',') + 1);
            }
            $bytes = base64_decode(preg_replace('/\s+/', '', $b64), true);
            if ($bytes === false || $bytes === '') {
                return response()->json(['error' => 'image_base64 is not valid base64.'], 422);
            }
        } else {
            return response()->json(['error' => 'Send a multipart "file" field or JSON "image_base64".'], 422);
        }

        [$media, $error] = ImagePipeline::store($bytes, asUpload: true);
        if ($error) {
            return response()->json(['error' => ucfirst($error).'.'], 422);
        }

        return response()->json([
            'upload_id' => $media->upload_token,
            'expires_at' => $media->expires_at->toIso8601String(),
            'mime' => $media->mime,
            'size' => $media->size,
            'hint' => 'Pass upload_id as image_upload_id to create_product, update_product, create_category, update_category or update_about_us.',
        ], 201);
    }
}
