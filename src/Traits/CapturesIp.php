<?php

declare(strict_types=1);

namespace Jeremykenedy\LaravelIpCapture\Traits;

use Jeremykenedy\LaravelIpCapture\Contracts\IpResolverInterface;

trait CapturesIp
{
    /**
     * Get the current client IP address.
     */
    public function captureIp(): string
    {
        return app(IpResolverInterface::class)->getClientIp();
    }

    /**
     * Set the signup IP address on the model.
     */
    public function setSignupIp(): static
    {
        if ($this->ipColumnEnabled('signup_ip_address')) {
            $this->signup_ip_address = $this->captureIp();
        }

        return $this;
    }

    /**
     * Set the signup confirmation IP address on the model.
     */
    public function setSignupConfirmationIp(): static
    {
        if ($this->ipColumnEnabled('signup_confirmation_ip_address')) {
            $this->signup_confirmation_ip_address = $this->captureIp();
        }

        return $this;
    }

    /**
     * Set the social media signup IP address on the model.
     */
    public function setSocialSignupIp(): static
    {
        if ($this->ipColumnEnabled('signup_sm_ip_address')) {
            $this->signup_sm_ip_address = $this->captureIp();
        }

        return $this;
    }

    /**
     * Set the admin action IP address on the model.
     */
    public function setAdminIp(): static
    {
        if ($this->ipColumnEnabled('admin_ip_address')) {
            $this->admin_ip_address = $this->captureIp();
        }

        return $this;
    }

    /**
     * Set the updated IP address on the model.
     */
    public function setUpdatedIp(): static
    {
        if ($this->ipColumnEnabled('updated_ip_address')) {
            $this->updated_ip_address = $this->captureIp();
        }

        return $this;
    }

    /**
     * Set the deleted IP address on the model.
     */
    public function setDeletedIp(): static
    {
        if ($this->ipColumnEnabled('deleted_ip_address')) {
            $this->deleted_ip_address = $this->captureIp();
        }

        return $this;
    }

    /**
     * Set a specific IP column to the current client IP.
     */
    public function setIpColumn(string $column): static
    {
        if ($this->ipColumnEnabled($column)) {
            $this->{$column} = $this->captureIp();
        }

        return $this;
    }

    /**
     * Check if an IP column is enabled in config.
     */
    protected function ipColumnEnabled(string $column): bool
    {
        return config("ip-capture.columns.{$column}", false) === true;
    }

    /**
     * Get all IP columns and their values.
     */
    public function getIpColumns(): array
    {
        $columns = [];

        foreach (array_keys(config('ip-capture.columns', [])) as $column) {
            if ($this->ipColumnEnabled($column) && isset($this->{$column})) {
                $columns[$column] = $this->{$column};
            }
        }

        return $columns;
    }
}
