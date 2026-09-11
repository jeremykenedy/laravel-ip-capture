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
        if (!$this->shouldWriteIpColumn($column)) {
            return $this;
        }

        // A supplied address is stored under the same rules as a resolved one,
        // so hashing and anonymizing are not bypassed by passing one in.
        $this->{$column} = $ip === null ? $this->captureIp() : IpCapture::prepare($ip);

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
     * Whether a column may be written, which the master switch also governs.
     */
    protected function shouldWriteIpColumn(string $column): bool
    {
        return IpCapture::enabled() && $this->ipColumnEnabled($column);
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
        if (!$this->shouldWriteIpColumn($column)) {
            return;
        }

        $ip = $this->captureIp();

        if ($ip === IpCapture::preparedNullIp() && !$this->ipColumnIsSafeToOverwrite($column)) {
            return;
        }

        $this->{$column} = $ip;
    }

    /**
     * Whether writing an unresolved address over this column loses anything.
     *
     * Compared against the stored form of the null IP, because hashing and
     * anonymizing apply to that too. A column missing from the attributes of a
     * persisted model was never loaded, so what it holds is unknown.
     */
    protected function ipColumnIsSafeToOverwrite(string $column): bool
    {
        if (!$this->exists) {
            return true;
        }

        if (!array_key_exists($column, $this->getAttributes())) {
            return false;
        }

        $stored = $this->{$column};

        return $stored === null || $stored === '';
    }
}
