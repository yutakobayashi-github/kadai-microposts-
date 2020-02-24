<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'password',
    ];

    
    protected $hidden = [
        'password', 'remember_token',
    ];
    
    public function microposts()
    {
        return $this->hasMany(Micropost::class);
    }
    
    public function followings()
    {
        return $this->belongsToMany(User::class, 'user_follow', 'user_id', 'follow_id')->withTimestamps();
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_follow', 'follow_id', 'user_id')->withTimestamps();
    }
    
    public function follow($userId)
    {
        // 既にフォローしているかの確認
        $exist = $this->is_following($userId);
        // 相手が自分自身ではないかの確認
        $its_me = $this->id == $userId;
    
        if ($exist || $its_me) {
            // 既にフォローしていれば何もしない
            return false;
        } else {
            // 未フォローであればフォローする
            $this->followings()->attach($userId);
            return true;
        }
    }
    
    public function unfollow($userId)
    {
        // 既にフォローしているかの確認
        $exist = $this->is_following($userId);
        // 相手が自分自身かどうかの確認
        $its_me = $this->id == $userId;
    
        if ($exist && !$its_me) {
            // 既にフォローしていればフォローを外す
            $this->followings()->detach($userId);
            return true;
        } else {
            // 未フォローであれば何もしない
            return false;
        }
    }
    
    public function is_following($userId)
    {
        return $this->followings()->where('follow_id', $userId)->exists();
    }
    
    public function feed_microposts()
    {
        $follow_user_ids = $this->followings()->pluck('users.id')->toArray();
        $follow_user_ids[] = $this->id;
        return Micropost::whereIn('user_id', $follow_user_ids);
    }
    
    
    
    
    // 一覧の取得
    public function favorites()
    {
        return $this->belongsToMany(Micropost::class, 'user_favorites', 'user_id', 'micropost_id')->withTimestamps();
    }
    
    
    // micropostIdで渡されたmicropost_idが既にお気に入り登録をしているのか調べるメソッド
    public function is_favoriting($micropostId)
    {
        return $this->favorites()->where('micropost_id', $micropostId)->exists();
    }
    
    
    // ユーザが特定の投稿内容をお気に入りに追加する機能
    public function favorite($micropostId) 
    {
        // 既にお気に入りに追加しているかの確認
        $exist = $this->is_favoriting($micropostId);
        // // 相手が自分自身ではないかの確認
        // $its_me = $this->id == $micropostId;
    
        if ($exist) {
            // 既にお気に入りに追加していれば何もしない
            return false;
        } else {
            // まだであればお気に入りに追加する
            $this->favorites()->attach($micropostId);
            return true;
        }
    }
    
    
    // ユーザがお気に入りを削除する機能
    public function unfavorite($micropostId)
    {
        // 既にお気に入りに追加しているかの確認
        $exist = $this->is_favoriting($micropostId);
        if ($exist) {
            // 既にお気に入りに追加していればフォローを外す
            $this->favorites()->detach($micropostId);
            return true;
        } else {
            // 未お気に入りに追加であれば何もしない
            return false;
        }
    }
}
