<?php

namespace App\Imports;

use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;

class StudentsImport implements SkipsOnFailure, ToCollection, WithHeadingRow, WithValidation
{
    use Importable, SkipsFailures;

    private int $schoolUserId;

    private int $successCount = 0;

    public function __construct(int $schoolUserId)
    {
        $this->schoolUserId = $schoolUserId;
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            DB::transaction(function () use ($row) {
                $studentUser = User::query()->create([
                    'name' => $row['full_name'],
                    'email' => null,
                    'password' => Str::random(32),
                    'role' => User::ROLE_SISWA,
                    'account_status' => 'active',
                ]);

                StudentProfile::query()->create([
                    'user_id' => $studentUser->id,
                    'school_user_id' => $this->schoolUserId,
                    'full_name' => $row['full_name'],
                    'nisn' => (string) $row['nisn'],
                    'major' => $row['major'],
                    'school_origin' => $row['school_origin'],
                    'graduation_status' => $row['graduation_status'],
                    'class_year' => (string) $row['class_year'],
                    'unique_code' => $this->generateUniqueCode(),
                    'phone_number' => isset($row['phone_number']) ? (string) $row['phone_number'] : null,
                    'address' => $row['address'] ?? null,
                ]);

                $this->successCount++;
            });
        }
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'nisn' => ['required', 'digits:10', 'unique:student_profiles,nisn'],
            'major' => ['required', 'string', 'max:100'],
            'school_origin' => ['required', 'string', 'max:255'],
            'graduation_status' => ['required', 'string', 'max:100'],
            'class_year' => ['required', 'digits:4'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
        ];
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    /**
     * @return array<int, array{row: int, attribute: string, errors: list<string>}>
     */
    public function getFormattedFailures(): array
    {
        return collect($this->failures())->map(fn (Failure $failure) => [
            'row' => $failure->row(),
            'attribute' => $failure->attribute(),
            'errors' => $failure->errors(),
        ])->values()->all();
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (StudentProfile::query()->where('unique_code', $code)->exists());

        return $code;
    }
}
