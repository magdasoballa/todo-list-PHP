<?php

namespace App\Http\Controllers;

use App\Models\Caregiver;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CaregiverController extends Controller
{
    public function index(Request $request)
    {
        $languages = explode(',', $request->input('languages', ''));
        $experience = $request->input('experience', '0-1');
        $age = $request->input('age', '');
        $specializations = explode(',', $request->input('specialization', ''));
        $survey = auth()->user()->survey;

        if (count($languages) === 0) {
            $languages = [''];
        }

        if (empty($specializations) || (count($specializations) === 1 && empty($specializations[0]))) {
            $specializations = null;
        }

        $experienceRanges = [
            '0-1' => [0, 1],
            '1-3' => [1, 3],
            '3-5' => [3, 5],
            '5-7' => [5, 7],
            '7+' => [7, PHP_INT_MAX],
        ];

        $query = Caregiver::query();

        if (!empty($languages)) {
            $query->where(function ($q) use ($languages) {
                foreach ($languages as $language) {
                    $q->orWhereRaw("caregivers.languages LIKE ?", ['%"' . $language . '%"']);
                }
            });
        }

        if (!empty($specializations)) {
            $query->where(function ($q) use ($specializations) {
                foreach ($specializations as $specialization) {
                    $q->orWhereRaw("JSON_CONTAINS(caregivers.specialization, ?, '$')", [json_encode([$specialization])]);
                }
            });
        }

        if (!empty($age)) {
            list($ageStart, $ageEnd) = explode('-', $age);
            $query->whereBetween('age', [$ageStart, $ageEnd]);
        }

        if (!isset($experienceRanges[$experience])) {
            $experience = '0-1';
        }

        if (isset($experienceRanges[$experience])) {
            $query->whereBetween('experience_years', $experienceRanges[$experience]);
        }

        $matchedCaregivers = $query->get();

        return Inertia::render('Survey/SurveyCompleted', [
            'matchedCaregivers' => $matchedCaregivers,
            'survey' => $survey,
        ]);
    }
}
