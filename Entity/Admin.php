<?php

namespace App\Entity;

use App\Repository\AdminRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=AdminRepository::class)
 * @ORM\Table(name="admin", options={"comment":"管理员表"})
 */
class Admin implements UserInterface
{
    /**
     *
     * @ORM\Id()
     * @ORM\Column(name="id", type="string")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="App\Doctrine\SortIdGenerator")
     */
    private $id;

    /**
     * @var UserAuth
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\UserAuth")
     * @ORM\JoinColumn(name="user_auth_id", referencedColumnName="id")
     */
    private $userAuth;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=30, nullable=true, options={"commnet":"管理员名称"})
     */
    private $name;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="请填写登录账号")
     * @Assert\Length(max="30", maxMessage="登录账号最大长度为30")
     * @Assert\Regex(pattern="/^\w{6,30}$/", message="登录账号格式错误，格式为6～30位英文字母")
     *
     *
     * @ORM\Column(name="account", type="string", length=30, options={"commnt":"登录账号"})
     */
    private $account;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_disable", type="boolean", options={"commnt":"是否禁用", "default":"0"})
     */
    private $isDisable = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_del", type="boolean", options={"comment":"是否删除", "default":"0"})
     */
    private $isDel = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_at", type="datetime", options={"comment":"创建时间"})
     */
    private $createAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_at", type="datetime", nullable=true, options={"comment":"更新时间"})
     */
    private $updateAt;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_built", type="boolean", options={"comment":"是否内置", "default":"0"})
     */
    private $isBuilt = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_super", type="boolean", options={"comment":"是否超级管理员", "default":"0"})
     */
    private $isSuper = false;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAccount(): ?string
    {
        return $this->account;
    }

    public function setAccount(string $account): self
    {
        $this->account = $account;

        return $this;
    }

    public function getIsDisable(): ?bool
    {
        return $this->isDisable;
    }

    public function setIsDisable(bool $isDisable): self
    {
        $this->isDisable = $isDisable;

        return $this;
    }

    public function getIsDel(): ?bool
    {
        return $this->isDel;
    }

    public function setIsDel(bool $isDel): self
    {
        $this->isDel = $isDel;

        return $this;
    }

    public function getCreateAt(): ?\DateTimeInterface
    {
        return $this->createAt;
    }

    public function setCreateAt(\DateTimeInterface $createAt): self
    {
        $this->createAt = $createAt;

        return $this;
    }

    public function getUpdateAt(): ?\DateTimeInterface
    {
        return $this->updateAt;
    }

    public function setUpdateAt(?\DateTimeInterface $updateAt): self
    {
        $this->updateAt = $updateAt;

        return $this;
    }

    public function getUserAuth(): ?UserAuth
    {
        return $this->userAuth;
    }

    public function setUserAuth(?UserAuth $userAuth): self
    {
        $this->userAuth = $userAuth;

        return $this;
    }

    public function getIsBuilt(): ?bool
    {
        return $this->isBuilt;
    }

    public function setIsBuilt(bool $isBuilt): self
    {
        $this->isBuilt = $isBuilt;

        return $this;
    }

    public function getIsSuper(): ?bool
    {
        return $this->isSuper;
    }

    public function setIsSuper(bool $isSuper): self
    {
        $this->isSuper = $isSuper;

        return $this;
    }

}