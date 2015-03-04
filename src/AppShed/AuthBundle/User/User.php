<?php
/**
 * Created by PhpStorm.
 * User: Vitaliy Pitvalo
 * Date: 2/27/15
 * Time: 1:53 PM
 */

namespace AppShed\AuthBundle\User;

use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface , \JsonSerializable
{
    private $id = null;
    private $name = null;
    private $username = null;
    private $email = null;
    private $params = [];
    private $client = null;
    private $roles = [];

    /**
     * @param array $user
     */
    public function __construct(array $user)
    {
        $this->setId($user['id']);
        $this->setName($user['name']);
        $this->setUsername($user['username']);
        $this->setEmail($user['email']);
        $this->setParams($user['params']);
        $this->setClient($user['client']);
        $this->setRoles($user['roles']);
    }


    /**
     * @return null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     * @return User
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;

    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param  $name
     * @return User
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;

    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param  $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;

    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param  $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;

    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param $params
     * @return User
     */
    public function setParams($params)
    {
        $this->params = $params;
        return $this;

    }

    /**
     * @return integer
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param  $client
     * @return User
     */
    public function setClient($client)
    {
        $this->client = $client;
        return $this;
    }


    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     * @return User
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string The password
     */
    public function getPassword()
    {
        // TODO: Implement getPassword() method.
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }


    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    public function jsonSerialize() {
        return [

        'id'=>   $this->getId(),
        'name'=> $this->getName(),
        'username'=> $this->getUsername(),
        'email'=> $this->getEmail(),
        'params'=>$this->getParams(),
        'client'=>$this->getClient(),
        'roles'=>$this->getRoles()
        ];
    }
}