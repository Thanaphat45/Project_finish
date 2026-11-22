<?php

namespace App\Http\Controllers;

use App\Models\Elderly;
use App\Support\Sql;
use App\Models\User;
use App\Models\BarthelAdl;
use App\Models\CareGiver;
use App\Models\CareInstruction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user)
            return redirect()->route('login');
        switch ($user->Type_Personnel) {
            case 'Admin':
                return $this->adminDashboard();
            case 'Staff':
                return $this->staffDashboard($request);
            case 'Doctor':
                return $this->doctorDashboard($request);
            default:
                return redirect()->route('welcome');
        }
    }

    private function adminDashboard()
    {
        $users = User::orderBy('ID_User', 'desc')->get();
        return view('dashboard', compact('users'));
    }

    private function staffDashboard(Request $request)
    {
        $search = $request->input('search');
        $groupFilter = $request->input('adl_group');
        $gender = $request->input('gender');
        $ageRange = $request->input('age_range');
        $createdFrom = $request->input('created_date_from');
        $createdTo = $request->input('created_date_to');

        $query = Elderly::with(['addressElderly', 'barthel_adl', 'care_giver', 'score_tai', 'care_instructions']);

        if ($search) {
            $query->where('Name_Elderly', 'LIKE', "%{$search}%");
        }
        if ($groupFilter) {
            $query->whereHas('barthel_adl', fn($q) => $q->where('Group_ADL', $groupFilter));
        }
        if ($gender) {
            $query->where('Gender', $gender);
        }
        if ($ageRange) {
            $this->applyAgeFilter($query, $ageRange);
        }
        if ($createdFrom) {
            $query->whereDate('created_at', '>=', $createdFrom);
        }
        if ($createdTo) {
            $query->whereDate('created_at', '<=', $createdTo);
        }

        // Bucket counts first, on a clone, so paginate()'s limit/offset can't leak in.
        // One query instead of seven: each round trip to the database costs ~100ms+.
        [$adl, $age] = $this->bucketCounts(clone $query);
        $adlGroups = [
            'กลุ่มติดสังคม' => $adl['กลุ่มติดสังคม'],
            'กลุ่มติดบ้าน' => $adl['กลุ่มติดบ้าน'],
            'กลุ่มติดเตียง' => $adl['กลุ่มติดเตียง'],
        ];
        $ageGroups = [
            'ช่วงอายุ 60-69' => $age['60-69'],
            'ช่วงอายุ 70-79' => $age['70-79'],
            'ช่วงอายุ 80-89' => $age['80-89'],
            'ช่วงอายุ 90+' => $age['90+'],
        ];

        $elderlies = $query->paginate(20);
        /** @var \Illuminate\Pagination\LengthAwarePaginator $elderlies */
        $elderlies->withQueryString();

        $elderlyLocations = $this->getElderlyLocations($elderlies);

        return view('dashboard', compact('elderlies', 'ageGroups', 'adlGroups', 'elderlyLocations'));
    }

    private function doctorDashboard(Request $request)
    {
        $user = Auth::user();
        // Dashboard filtering
        $query = Elderly::with(['barthel_adl', 'care_giver', 'care_instructions']);

        // Doctor's assigned group filter
        if ($group = $user->doctorAdlGroup()) {
            $query->whereHas('barthel_adl', fn ($q) => $q->where('Group_ADL', $group));
        }

        if ($request->filled('adl_group')) {
            $query->whereHas('barthel_adl', fn($q) => $q->where('Group_ADL', $request->adl_group));
        }
        if ($request->filled('age_range')) {
            $this->applyAgeFilter($query, $request->age_range);
        }

        [$adl, $age] = $this->bucketCounts(clone $query);

        $elderlys = $query->paginate(20);
        /** @var \Illuminate\Pagination\LengthAwarePaginator $elderlys */
        $elderlys->withQueryString();

        $stats = [
            'total_patients' => $elderlys->total(),
            'pending_ci' => CareInstruction::whereNull('Confirm')->count(),
            'today_assessed' => Elderly::whereHas('care_giver', fn($q) => $q->where('Date_CG', now()->toDateString()))->count()
        ];

        $chartData = [
            'adl_distribution' => [
                'ติดสังคม' => $adl['กลุ่มติดสังคม'],
                'ติดบ้าน' => $adl['กลุ่มติดบ้าน'],
                'ติดเตียง' => $adl['กลุ่มติดเตียง'],
            ],
            'age_distribution' => $age,
        ];

        return view('dashboard', compact('elderlys', 'stats', 'chartData'));
    }

    /**
     * ADL-group and age-range counts for an already-filtered elderly query, in
     * a single round trip. The ADL group is each elderly's latest assessment
     * (highest ID_ADL), matching the barthel_adl() latestOfMany relation.
     *
     * @return array{0: array<string,int>, 1: array<string,int>}
     */
    private function bucketCounts($query): array
    {
        $latestGroup = BarthelAdl::select('Group_ADL')
            ->whereColumn('barthel_adls.ID_Elderly', 'elderlys.ID_Elderly')
            ->orderByDesc('ID_ADL')
            ->limit(1);

        $rows = $query->toBase()
            ->select('elderlys.ID_Elderly')
            ->selectSub($latestGroup->toBase(), 'grp')
            ->selectRaw(Sql::age() . ' as age');

        $sum = fn(string $cond) => "COALESCE(SUM(CASE WHEN {$cond} THEN 1 ELSE 0 END), 0)";
        $r = \Illuminate\Support\Facades\DB::query()->fromSub($rows, 't')
            ->selectRaw($sum('grp = ?') . ' as social', ['กลุ่มติดสังคม'])
            ->selectRaw($sum('grp = ?') . ' as home', ['กลุ่มติดบ้าน'])
            ->selectRaw($sum('grp = ?') . ' as bed', ['กลุ่มติดเตียง'])
            ->selectRaw($sum('age BETWEEN 60 AND 69') . ' as a60')
            ->selectRaw($sum('age BETWEEN 70 AND 79') . ' as a70')
            ->selectRaw($sum('age BETWEEN 80 AND 89') . ' as a80')
            ->selectRaw($sum('age >= 90') . ' as a90')
            ->first();

        return [
            ['กลุ่มติดสังคม' => (int) $r->social, 'กลุ่มติดบ้าน' => (int) $r->home, 'กลุ่มติดเตียง' => (int) $r->bed],
            ['60-69' => (int) $r->a60, '70-79' => (int) $r->a70, '80-89' => (int) $r->a80, '90+' => (int) $r->a90],
        ];
    }

    private function applyAgeFilter($query, $range)
    {
        switch ($range) {
            case '60-69':
                $query->whereRaw(Sql::age() . ' BETWEEN 60 AND 69');
                break;
            case '70-79':
                $query->whereRaw(Sql::age() . ' BETWEEN 70 AND 79');
                break;
            case '80-89':
                $query->whereRaw(Sql::age() . ' BETWEEN 80 AND 89');
                break;
            case '90+':
                $query->whereRaw(Sql::age() . ' >= 90');
                break;
        }
    }

    private function getElderlyLocations($elderlies)
    {
        $locations = [];
        foreach ($elderlies as $e) {
            if ($e->addressElderly && $e->addressElderly->Latitude_position && $e->addressElderly->Longitude_position) {
                $locations[] = [
                    'latitude' => $e->addressElderly->Latitude_position,
                    'longitude' => $e->addressElderly->Longitude_position,
                    'name' => $e->Name_Elderly,
                    'address' => $e->Address,
                    'adlGroup' => optional($e->barthel_adl)->Group_ADL ?: 'ยังไม่ได้ประเมิน',
                ];
            }
        }
        return $locations;
    }
}
