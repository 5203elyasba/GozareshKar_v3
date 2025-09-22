<?php
class ReportCalculator {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function calculateForUser(int $user_id) {
        try {
            // Fetch user base data
            $user_sql = "SELECT daily_hours_goal, annual_leave_days FROM users WHERE id = :id";
            $user_stmt = $this->pdo->prepare($user_sql);
            $user_stmt->execute(['id' => $user_id]);
            $user_data = $user_stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user_data) {
                return ['error' => 'User not found'];
            }

            // Fetch all time logs
            $time_sql = "SELECT log_date, start_time, end_time, log_type FROM time_logs WHERE user_id = :user_id";
            $time_stmt = $this->pdo->prepare($time_sql);
            $time_stmt->execute(['user_id' => $user_id]);
            $time_logs = $time_stmt->fetchAll(PDO::FETCH_ASSOC);

            // Fetch all leave logs
            $leave_sql = "SELECT COUNT(*) as count FROM leave_logs WHERE user_id = :user_id";
            $leave_stmt = $this->pdo->prepare($leave_sql);
            $leave_stmt->execute(['user_id' => $user_id]);
            $leave_count = $leave_stmt->fetchColumn();

            // --- Calculations ---
            $total_work_seconds = 0;
            $total_break_seconds = 0;
            $days_worked = count(array_unique(array_column($time_logs, 'log_date')));

            foreach ($time_logs as $log) {
                $start = new DateTime($log['start_time']);
                $end = new DateTime($log['end_time']);
                $diff_seconds = $end->getTimestamp() - $start->getTimestamp();

                if ($log['log_type'] === 'work') {
                    $total_work_seconds += $diff_seconds;
                } else {
                    $total_break_seconds += $diff_seconds;
                }
            }

            $net_work_seconds = $total_work_seconds - $total_break_seconds;

            $total_hours_goal = $days_worked * $user_data['daily_hours_goal'] * 3600;
            $overtime_undertim_seconds = $net_work_seconds - $total_hours_goal;

            $remaining_leave_days = $user_data['annual_leave_days'] - $leave_count;

            return [
                'success' => true,
                'total_work_hours' => round($net_work_seconds / 3600, 2),
                'total_overtime_undertim_hours' => round($overtime_undertim_seconds / 3600, 2),
                'remaining_leave_days' => $remaining_leave_days,
                'days_worked' => $days_worked
            ];

        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Calculation error: ' . $e->getMessage()];
        }
    }
}
?>
