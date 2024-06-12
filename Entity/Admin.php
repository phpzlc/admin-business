<?php

namespace App\Entity;

use App\Repository\AdminRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use PHPZlc\PHPZlc\Doctrine\ORM\Mapping\OuterColumn;
use Symfony\Component\Validator\Constraints as Assert;
use App\Business\AuthBusiness\UserInterface;
use PHPZlc\PHPZlc\Doctrine\SortIdGenerator;

#[ORM\Entity(repositoryClass: AdminRepository::class)]
#[ORM\Table(name: "admin", options:["comment" => "管理员表"])]
class Admin implements UserInterface
{
    #[ORM\Id]
    #[ORM\Column(name: "id", type: "string")]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: SortIdGenerator::class)]
    private ?string $id = null;

    #[ORM\ManyToOne(targetEntity:"App\Entity\UserAuth")]
    #[ORM\JoinColumn(name:"user_auth_id", referencedColumnName:"id")]
    private ?UserAuth $userAuth = null;

    #[ORM\Column(name:"name", type:"string", length:30, nullable:true, options:["comment" => "管理员名称"])]
    private ?string $name = null;

    #[ORM\Column(name: "account", type: "string", length: 30, options: ["comment" => "登录账号"])]
    #[Assert\NotBlank(message: "请填写登录账号")]
    #[Assert\Length(max: 30, maxMessage: "登录账号最大长度为30")]
    #[Assert\Regex(pattern: "/^\w{6,30}$/", message: "登录账号格式错误，格式为6～30位英文字母")]
    private ?string $account = null;

    #[ORM\Column(name:"is_disable", type: "smallint", options: ["comment" => "是否禁用", "default" => 0])]
    private int $isDisable = 0;

    #[ORM\Column(name:"is_del", type: "smallint", options: ["comment" => "是否删除", "default" => 0])]
    private int $isDel = 0;

    #[ORM\Column(name:"is_built", type: "smallint", options: ["comment" => "是否内置", "default" => 0])]
    private int $isBuilt = 0;

    #[ORM\Column(name:"is_super", type: "smallint", options: ["comment" => "是否超级管理员", "default" => 0])]
    private int $isSuper = 0;

    #[OuterColumn(name: "role_string", type: "string", sql: "(IF(sql_pre.is_super = 1,'超级管理员', (select GROUP_CONCAT(r.name) from role r where id in (select role_id from user_auth_role uar where uar.user_auth_id = sql_pre.user_auth_id))))", options: ["comment" => "是否超级管理员"])]
    private ?string $roleString = null;

    #[ORM\Column(name: "update_at", type: "datetime", nullable:true, options:["comment" => "更新时间"])]
    private ?\DateTimeInterface $updateAt = null;

    #[ORM\Column(name: "create_at", type: "datetime", options:["comment" => "创建时间"])]
    private ?\DateTimeInterface $createAt;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getAccount(): ?string
    {
        return $this->account;
    }

    public function setAccount(string $account): static
    {
        $this->account = $account;

        return $this;
    }

    public function getIsDisable(): ?bool
    {
        return (bool)$this->isDisable;
    }

    public function setIsDisable(bool $isDisable): static
    {
        $this->isDisable = (int)$isDisable;

        return $this;
    }

    public function getIsDel(): ?bool
    {
        return (bool)$this->isDel;
    }

    public function setIsDel(bool $isDel): static
    {
        $this->isDel = (int)$isDel;

        return $this;
    }

    public function getIsBuilt(): ?bool
    {
        return (bool)$this->isBuilt;
    }

    public function setIsBuilt(bool $isBuilt): static
    {
        $this->isBuilt = (int)$isBuilt;

        return $this;
    }

    public function getIsSuper(): ?bool
    {
        return (bool)$this->isSuper;
    }

    public function setIsSuper(bool $isSuper): static
    {
        $this->isSuper = (int)$isSuper;

        return $this;
    }

    public function getUpdateAt(): ?\DateTimeInterface
    {
        return $this->updateAt;
    }

    public function setUpdateAt(?\DateTimeInterface $updateAt): static
    {
        $this->updateAt = $updateAt;

        return $this;
    }

    public function getCreateAt(): ?\DateTimeInterface
    {
        return $this->createAt;
    }

    public function setCreateAt(\DateTimeInterface $createAt): static
    {
        $this->createAt = $createAt;

        return $this;
    }

    public function getUserAuth(): ?UserAuth
    {
        return $this->userAuth;
    }

    public function setUserAuth(?UserAuth $userAuth): static
    {
        $this->userAuth = $userAuth;

        return $this;
    }

    public function getRoleString(): ?string
    {
        return $this->roleString;
    }
}
