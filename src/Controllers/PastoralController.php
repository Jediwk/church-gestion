<?php
namespace App\Controllers;

use Core\Controller;
use Core\View;
use App\Models\Member;
use App\Models\Family;
use App\Models\Attendance;

class PastoralController extends Controller {
    public function __construct() {
        parent::__construct();
        $this->requirePermission('read_all');
    }

    public function index() {
        return View::render('pastoral/dashboard', [
            'title' => 'Dashboard Pastoral'
        ]);
    }

    public function dashboardData() {
        $period = $_GET['period'] ?? 'month';
        
        // Calcul des dates selon la période
        list($start, $end) = $this->calculateDateRange($period);

        // Modèles
        $memberModel = new Member();
        $familyModel = new Family();
        $attendanceModel = new Attendance();

        // Statistiques des membres
        $totalMembers = $memberModel->getTotal();
        $activeMembers = $memberModel->getActiveTotal();
        $memberGrowth = $this->calculateGrowth($memberModel->getTotal($start), $totalMembers);

        // Statistiques des familles
        $totalFamilies = $familyModel->getTotal();
        $averageFamilySize = $familyModel->getAverageSize();

        // Statistiques de présence
        $averageAttendance = $attendanceModel->getAverageAttendance($start, $end);
        $attendanceTrend = $this->getAttendanceTrend($attendanceModel, $start, $end);

        // Taux d'engagement
        $engagementRate = ($activeMembers / $totalMembers) * 100;

        // Données pour les graphiques
        $membershipData = $memberModel->getMembershipTrend($start, $end);
        $attendanceData = $attendanceModel->getAttendanceTrend($start, $end);
        $ageDistribution = $memberModel->getAgeDistribution();
        $genderDistribution = $memberModel->getGenderDistribution();
        $locationDistribution = $memberModel->getLocationDistribution();

        return json_encode([
            'success' => true,
            'data' => [
                'kpis' => [
                    'total_members' => $totalMembers,
                    'member_growth' => $memberGrowth,
                    'total_families' => $totalFamilies,
                    'average_family_size' => $averageFamilySize,
                    'average_attendance' => $averageAttendance,
                    'attendance_trend' => $attendanceTrend,
                    'engagement_rate' => round($engagementRate, 2),
                    'active_members' => $activeMembers
                ],
                'charts' => [
                    'membership' => $membershipData,
                    'attendance' => $attendanceData,
                    'age_distribution' => $ageDistribution,
                    'gender_distribution' => $genderDistribution,
                    'location_distribution' => $locationDistribution
                ]
            ]
        ]);
    }

    private function calculateDateRange($period) {
        $end = new \DateTime();
        $start = new \DateTime();

        switch ($period) {
            case 'month':
                $start->modify('first day of this month');
                break;
            case 'quarter':
                $currentQuarter = ceil($end->format('n') / 3);
                $start->setDate($end->format('Y'), ($currentQuarter - 1) * 3 + 1, 1);
                break;
            case 'year':
                $start->modify('first day of january this year');
                break;
            case 'all':
                $start = null;
                break;
        }

        return [
            $start ? $start->format('Y-m-d') : null,
            $end->format('Y-m-d')
        ];
    }

    private function calculateGrowth($previous, $current) {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        return round((($current - $previous) / $previous) * 100, 2);
    }

    private function getAttendanceTrend($attendanceModel, $start, $end) {
        $trend = $attendanceModel->getTrend($start, $end);
        if ($trend > 0) {
            return "En hausse de {$trend}%";
        } elseif ($trend < 0) {
            return "En baisse de " . abs($trend) . "%";
        }
        return "Stable";
    }
}
