<?php

namespace App\Http\Controllers\Api\FlaskEmotion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class EmotionDetectionController extends Controller
{
        public function predictEmotion(Request $request)
    {
        // Validate the request (ensure an image is uploaded)
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Get the image file and convert to base64
        $image = $request->file('image');
        $base64Image = base64_encode(file_get_contents($image->path()));
        $imageData = 'data:image/jpeg;base64,' . $base64Image;

        // Send request to Flask API
        $client = new Client();
        try {
            $response = $client->post('https://vitaglyph-flask-nh8od.ondigitalocean.app/predict', [
                'json' => ['image' => $imageData],
                'headers' => ['Content-Type' => 'application/json'],
            ]);

            $result = json_decode($response->getBody(), true);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
