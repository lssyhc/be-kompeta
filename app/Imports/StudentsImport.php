<?php

namespace App\Imports;

use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentsImport implements ToCollection, WithHeadingRow
{
    use Importable;

    private int $schoolUserId;

    private string $schoolName;

    private int $successCount = 0;

    /** @var list<string> */
    private array $invalidFields = [];

    public function __construct(int $schoolUserId, string $schoolName)
    {
        $this->schoolUserId = $schoolUserId;
        $this->schoolName = $schoolName;
    }

    public function collection(Collection $rows): void
    {
        $allInvalidFields = [];

        $existingNisns = StudentProfile::query()->pluck('nisn')->toArray();
        $fileNisns = [];

        foreach ($rows as $index => $row) {
            $rowData = $row->toArray();

            $validator = Validator::make($rowData, [
                'full_name' => ['required', 'string', 'max:255'],
                'nisn' => ['required', 'digits:10'],
                'major' => ['required', 'string', 'max:100'],
                'graduation_status' => ['required', 'string', 'max:100'],
                'class_year' => ['required', 'digits:4'],
                'address' => ['nullable', 'string'],
            ]);

            if ($validator->fails()) {
                $allInvalidFields = array_merge($allInvalidFields, array_keys($validator->errors()->toArray()));

                continue;
            }

            $nisn = (string) $rowData['nisn'];

            if (in_array($nisn, $existingNisns) || in_array($nisn, $fileNisns)) {
                $allInvalidFields[] = 'nisn';

                continue;
            }

            $fileNisns[] = $nisn;
        }

        $this->invalidFields = array_values(array_unique($allInvalidFields));

        if (count($this->invalidFields) > 0) {
            return;
        }

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
                    'school_origin' => $this->schoolName,
                    'graduation_status' => $row['graduation_status'],
                    'class_year' => (string) $row['class_year'],
                    'unique_code' => $this->generateUniqueCode(),
                    'photo_profile_path' => User::DEFAULT_PROFILE_PHOTO_PATH,
                    'socials' => StudentProfile::DEFAULT_SOCIALS,
                    'address' => $row['address'] ?? null,
                ]);

                $this->successCount++;
            });
        }
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    /**
     * @return list<string>
     */
    public function getInvalidFields(): array
    {
        return $this->invalidFields;
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (StudentProfile::query()->where('unique_code', $code)->exists());

        return $code;
    }
}
