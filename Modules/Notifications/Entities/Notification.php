<?php

namespace Modules\Notifications\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'title',
        'description',
        'user_id',
        'notifiable_type',   // نوع الكيان المرتبط (Team_Ownerinv, Team_Usesrinv)
        'notifiable_id',     // رقم الكيان المرتبط
        'type',              // نوع الإشعار من enum
        'read_at',           // تاريخ القراءة
    ];

    // أنواع الإشعارات
    const TYPE = [
        'INVITE_RECEIVED' => 'invite_received',   // وصلت دعوة
        'INVITE_ACCEPTED' => 'invite_accepted',   // تم قبول دعوة
        'INVITE_REJECTED' => 'invite_rejected',   // تم رفض دعوة
        'PLAYER_REPORTED' => 'player_reported', // تم الإبلاغ عن لاعب
        'PLAYER_NOTIFIED' => 'player_notified', // تم إشعار اللاعب
       'PLAYER_BANNED'   => 'player_banned',   // تم حظر اللاعب
        'PLAYER_UNBANNED'   => 'player_unbanned',
    ];

    // العلاقة مع المستخدم المستلم
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    // العلاقة polymorphic مع أي كيان مرتبط
    public function notifiable()
    {
        return $this->morphTo();
    }

    // Scope للإشعارات غير المقروءة
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    // Scope للإشعارات المقروءة
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    // دالة لتحديد إن كان الإشعار مقروء
    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }
}
