<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'employee_id',
        'document_number',
        'title',
        'description',
        'document_type',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'mime_type',
        'issue_date',
        'expiry_date',
        'is_required',
        'is_verified',
        'verified_by',
        'verified_at',
        'verification_notes',
        'status',
        'notes',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'is_required' => 'boolean',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Scopes
    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeByDocumentType($query, $type)
    {
        return $query->where('document_type', $type);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    public function scopePendingVerification($query)
    {
        return $query->where('status', 'pending_verification');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeUnverified($query)
    {
        return $query->where('is_verified', false);
    }

    public function scopeByFileType($query, $fileType)
    {
        return $query->where('file_type', $fileType);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('issue_date', [$startDate, $endDate]);
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        $expiryDate = now()->addDays($days)->toDateString();
        return $query->whereNotNull('expiry_date')
                    ->where('expiry_date', '<=', $expiryDate)
                    ->where('expiry_date', '>=', now()->toDateString());
    }

    public function scopeByUploader($query, $uploaderId)
    {
        return $query->where('uploaded_by', $uploaderId);
    }

    public function scopeByVerifier($query, $verifierId)
    {
        return $query->where('verified_by', $verifierId);
    }

    // Methods
    public function getFullNameAttribute()
    {
        return $this->employee->full_name ?? 'Unknown Employee';
    }

    public function getVerifierNameAttribute()
    {
        return $this->verifiedBy->name ?? 'Not verified';
    }

    public function getUploaderNameAttribute()
    {
        return $this->uploadedBy->name ?? 'Unknown';
    }

    public function getDocumentTypeTextAttribute()
    {
        return match($this->document_type) {
            'id_card' => 'ID Card',
            'passport' => 'Passport',
            'visa' => 'Visa',
            'work_permit' => 'Work Permit',
            'contract' => 'Contract',
            'offer_letter' => 'Offer Letter',
            'resume' => 'Resume',
            'certificate' => 'Certificate',
            'medical' => 'Medical',
            'insurance' => 'Insurance',
            'tax' => 'Tax',
            'bank' => 'Bank',
            'other' => 'Other',
            default => 'Unknown',
        };
    }

    public function getFileSizeDisplayAttribute()
    {
        if ($this->file_size < 1024) {
            return $this->file_size . ' B';
        } elseif ($this->file_size < 1024 * 1024) {
            return round($this->file_size / 1024, 2) . ' KB';
        } elseif ($this->file_size < 1024 * 1024 * 1024) {
            return round($this->file_size / (1024 * 1024), 2) . ' MB';
        } else {
            return round($this->file_size / (1024 * 1024 * 1024), 2) . ' GB';
        }
    }

    public function getDateRangeAttribute()
    {
        if ($this->issue_date && $this->expiry_date) {
            return $this->issue_date->format('M d, Y') . ' - ' . $this->expiry_date->format('M d, Y');
        } elseif ($this->issue_date) {
            return 'From ' . $this->issue_date->format('M d, Y');
        }
        return 'No date range';
    }

    public function getDurationAttribute()
    {
        if ($this->issue_date && $this->expiry_date) {
            return $this->issue_date->diffInDays($this->expiry_date) + 1;
        }
        return null;
    }

    public function getDaysUntilExpiryAttribute()
    {
        if ($this->expiry_date) {
            return $this->expiry_date->diffInDays(now(), false);
        }
        return null;
    }

    public function getDaysSinceIssueAttribute()
    {
        if ($this->issue_date) {
            return $this->issue_date->diffInDays(now(), false);
        }
        return null;
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isExpired()
    {
        return $this->status === 'expired';
    }

    public function isPendingVerification()
    {
        return $this->status === 'pending_verification';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function isRequired()
    {
        return $this->is_required;
    }

    public function isVerified()
    {
        return $this->is_verified;
    }

    public function isUnverified()
    {
        return !$this->is_verified;
    }

    public function isExpiringSoon($days = 30)
    {
        if (!$this->expiry_date) return false;
        
        $expiryDate = now()->addDays($days)->toDateString();
        return $this->expiry_date <= $expiryDate && $this->expiry_date >= now()->toDateString();
    }

    public function isOverdue()
    {
        return $this->expiry_date && $this->expiry_date < now()->toDateString();
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'active' => 'success',
            'expired' => 'danger',
            'pending_verification' => 'warning',
            'rejected' => 'danger',
            default => 'secondary',
        };
    }

    public function getDocumentTypeBadgeAttribute()
    {
        return match($this->document_type) {
            'id_card' => 'primary',
            'passport' => 'success',
            'visa' => 'info',
            'work_permit' => 'warning',
            'contract' => 'dark',
            'offer_letter' => 'secondary',
            'resume' => 'info',
            'certificate' => 'success',
            'medical' => 'danger',
            'insurance' => 'primary',
            'tax' => 'warning',
            'bank' => 'success',
            'other' => 'secondary',
            default => 'secondary',
        };
    }

    public function getRequiredBadgeAttribute()
    {
        return $this->is_required ? 'danger' : 'secondary';
    }

    public function getVerifiedBadgeAttribute()
    {
        return $this->is_verified ? 'success' : 'warning';
    }

    public function getFileTypeBadgeAttribute()
    {
        return match(strtolower($this->file_type)) {
            'pdf' => 'danger',
            'doc', 'docx' => 'primary',
            'xls', 'xlsx' => 'success',
            'ppt', 'pptx' => 'warning',
            'jpg', 'jpeg', 'png', 'gif' => 'info',
            'txt' => 'secondary',
            default => 'secondary',
        };
    }

    public function getExpiryStatusAttribute()
    {
        if (!$this->expiry_date) return 'No Expiry';
        if ($this->isOverdue()) return 'Expired';
        if ($this->isExpiringSoon(7)) return 'Expiring Soon (7 days)';
        if ($this->isExpiringSoon(30)) return 'Expiring Soon (30 days)';
        return 'Valid';
    }

    public function getExpiryStatusBadgeAttribute()
    {
        return match($this->expiry_status) {
            'No Expiry' => 'success',
            'Expired' => 'danger',
            'Expiring Soon (7 days)' => 'danger',
            'Expiring Soon (30 days)' => 'warning',
            'Valid' => 'success',
            default => 'secondary',
        };
    }

    public function getFileUrlAttribute()
    {
        return asset('storage/' . $this->file_path);
    }

    public function getDownloadUrlAttribute()
    {
        return route('employee-documents.download', $this->id);
    }

    public function getPreviewUrlAttribute()
    {
        if (in_array(strtolower($this->file_type), ['jpg', 'jpeg', 'png', 'gif', 'pdf'])) {
            return $this->file_url;
        }
        return null;
    }

    public function getDescriptionPreviewAttribute()
    {
        if ($this->description) {
            return strlen($this->description) > 100 
                ? substr($this->description, 0, 100) . '...' 
                : $this->description;
        }
        return 'No description';
    }

    public function getNotesPreviewAttribute()
    {
        if ($this->notes) {
            return strlen($this->notes) > 100 
                ? substr($this->notes, 0, 100) . '...' 
                : $this->notes;
        }
        return 'No notes';
    }

    public function getVerificationNotesPreviewAttribute()
    {
        if ($this->verification_notes) {
            return strlen($this->verification_notes) > 100 
                ? substr($this->verification_notes, 0, 100) . '...' 
                : $this->verification_notes;
        }
        return 'No verification notes';
    }

    public function getFormattedIssueDateAttribute()
    {
        return $this->issue_date ? $this->issue_date->format('M d, Y') : 'Not set';
    }

    public function getFormattedExpiryDateAttribute()
    {
        return $this->expiry_date ? $this->expiry_date->format('M d, Y') : 'No expiry';
    }

    public function getFormattedVerifiedDateAttribute()
    {
        return $this->verified_at ? $this->verified_at->format('M d, Y H:i') : 'Not verified';
    }

    public function getFormattedUploadDateAttribute()
    {
        return $this->created_at ? $this->created_at->format('M d, Y H:i') : 'Unknown';
    }

    public function canBeVerified()
    {
        return $this->isPendingVerification();
    }

    public function canBeRejected()
    {
        return $this->isPendingVerification();
    }

    public function canBeRenewed()
    {
        return $this->isExpired() || $this->isExpiringSoon();
    }

    public function canBeDownloaded()
    {
        return $this->isActive() || $this->isVerified();
    }

    public function canBePreviewed()
    {
        return $this->preview_url !== null;
    }

    public function getFileIconAttribute()
    {
        return match(strtolower($this->file_type)) {
            'pdf' => 'file-text',
            'doc', 'docx' => 'file-text',
            'xls', 'xlsx' => 'file-spreadsheet',
            'ppt', 'pptx' => 'file-presentation',
            'jpg', 'jpeg', 'png', 'gif' => 'file-image',
            'txt' => 'file-text',
            default => 'file',
        };
    }

    public function getFileTypeDisplayAttribute()
    {
        return strtoupper($this->file_type);
    }

    public function getMimeTypeDisplayAttribute()
    {
        return $this->mime_type ?: 'Unknown';
    }

    public function getIsImageAttribute()
    {
        return in_array(strtolower($this->file_type), ['jpg', 'jpeg', 'png', 'gif']);
    }

    public function getIsPdfAttribute()
    {
        return strtolower($this->file_type) === 'pdf';
    }

    public function getIsDocumentAttribute()
    {
        return in_array(strtolower($this->file_type), ['doc', 'docx', 'pdf', 'txt']);
    }

    public function getIsSpreadsheetAttribute()
    {
        return in_array(strtolower($this->file_type), ['xls', 'xlsx']);
    }

    public function getIsPresentationAttribute()
    {
        return in_array(strtolower($this->file_type), ['ppt', 'pptx']);
    }
}
