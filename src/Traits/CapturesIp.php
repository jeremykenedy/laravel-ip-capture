<?php

declare(strict_types=1);

namespace Jeremykenedy\LaravelIpCapture\Traits;

use Jeremykenedy\LaravelIpCapture\Contracts\IpResolverInterface;
use Jeremykenedy\LaravelIpCapture\Support\IpCapture;

trait CapturesIp
{
    /**
     * Wire the configured model events when automatic capture is switched on.
     *
     * Eloquent calls this once per model class, so the configuration has to be
     * in place before the model is first used.
     */
    public static function bootCapturesIp(): void
    {
        if (!IpCapture::autoCaptureEnabled()) {
            return;
        }

        foreach (IpCapture::autoCaptureEvents() as $event => $column) {
            static::registerModelEvent($event, static function (self $model) use ($column): void {
                $model->captureIpAutomatically($column);
            });
        }
    }

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
        return $this->setIpColumn('signup_ip_address');
    }

    /**
     * Set the signup confirmation IP address on the model.
     */
    public function setSignupConfirmationIp(): static
    {
        return $this->setIpColumn('signup_confirmation_ip_address');
    }

    /**
     * Set the social media signup IP address on the model.
     */
    public function setSocialSignupIp(): static
    {
        return $this->setIpColumn('signup_sm_ip_address');
    }

    /**
     * Set the admin action IP address on the model.
     */
    public function setAdminIp(): static
    {
        return $this->setIpColumn('admin_ip_address');
    }

    /**
     * Set the updated IP address on the model.
     */
    public function setUpdatedIp(): static
    {
        return $this->setIpColumn('updated_ip_address');
    }

    /**
     * Set the deleted IP address on the model.
     */
    public function setDeletedIp(): static
    {
        return $this->setIpColumn('deleted_ip_address');
    }

    /**
     * Set a specific IP column to the current client IP.
     */
    public function setIpColumn(string $column, ?string $ip = null): static
    {
        if ($this->ipColumnEnabled($column)) {
            $this->{$column} = $ip ?? $this->captureIp();
        }

        return $this;
    }

    /**
     * Check if an IP column is enabled in config.
     */
    protected function ipColumnEnabled(string $column): bool
    {
        return IpCapture::columnEnabled($column);
    }

    /**
     * Get all IP columns and their values.
     *
     * @return array<string, mixed>
     */
    public function getIpColumns(): array
    {
        $columns = [];

        foreach (array_keys(IpCapture::columns()) as $column) {
            if ($this->ipColumnEnabled($column) && isset($this->{$column})) {
                $columns[$column] = $this->{$column};
            }
        }

        return $columns;
    }

    /**
     * Capture during a model event, keeping any address already stored when
     * the current context has no address to offer, such as a queued job.
     */
    protected function captureIpAutomatically(string $column): void
    {
        if (!$this->ipColumnEnabled($column)) {
            return;
        }

        $ip = $this->captureIp();
        $stored = $this->{$column};

        if ($ip === IpCapture::nullIp() && $stored !== null && $stored !== '') {
            return;
        }

        $this->{$column} = $ip;
    }
}
