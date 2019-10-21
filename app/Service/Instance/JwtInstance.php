<?php

declare(strict_types=1);

namespace App\Service\Instance;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Model\Dao\SysUserDao;
use App\Model\SysUser;
use Firebase\JWT\JWT;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Traits\StaticInstance;

class JwtInstance
{
    use StaticInstance;

    const KEY = 'NoteBook';

    /**
     * @var int
     */
    public $user_id;

    /**
     * @var SysUser
     */
    public $sysUser;

    public function encode(SysUser $sysUser)
    {
        $this->user_id = $sysUser->user_id;
        $this->sysUser = $sysUser;

        return JWT::encode([
            'iss' => 'xxx.com', //签发者 可选
            'iat' => time(), //签发时间
            'id' => $sysUser->user_id
        ], self::KEY);
    }

    public function decode(string $token): self
    {
        try {
            $decoded = (array)JWT::decode($token, self::KEY, ['HS256']);
        } catch (\Throwable $exception) {
            return $this;
        }
        if ($id = $decoded['id'] ?? null) {
            $this->user_id = $id;
            $this->sysUser = ApplicationContext::getContainer()->get(SysUserDao::class)->first($id);
        }
        return $this;
    }

    public function build(): self
    {
        if (empty($this->user_id)) {
            throw new BusinessException(ErrorCode::TOKEN_INVALID);
        }
        return $this;
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->user_id;
    }

    /**
     * @return SysUser
     */
    public function getSysUser(): ?SysUser
    {
        if ($this->sysUser === null && $this->user_id) {
            $this->sysUser = di()->get(SysUserDao::class)->first($this->user_id);
        }
        return $this->sysUser;
    }
}
