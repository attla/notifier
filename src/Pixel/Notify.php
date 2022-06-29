<?php

namespace Attla\Notifier\Pixel;

use Attla\Jwt;

class Notify
{
    /**
     * The pixel endpoint
     *
     * @var string
     */
    protected string $endpoint;

    /**
     * The pixel identifier
     *
     * @var string
     */
    protected string $identifier;

    /**
     * The pixel payload
     *
     * @var array|\stdClass
     */
    protected array|\stdClass $payload;

    /**
     * The pixel secret token
     *
     * @var string
     */
    protected string $secret;

    /**
     * The pixel attempts
     *
     * @var int
     */
    protected int $tries;

    /**
     * How many minutes there should be between pixel notifier attempts
     *
     * @var int
     */
    protected int $backoff;

    private function __construct()
    {
    }

    /**
     * Create a new pixel notifier instance
     *
     * @return static
     */
    public static function create(): static
    {
        $config = config();
        $pixel = new static();
        $notifier = $config->get('notifier', []);

        $pixel->endpoint($notifier['server']['endpoint'] ?? '');
        $pixel->secret($notifier['server']['secret'] ?? '');
        $pixel->tries($notifier['tries'] ?? 3);
        $pixel->backoff($notifier['backoff'] ?? 15);

        return $pixel;
    }

    /**
     * Alias of create
     *
     * @return static
     */
    public static function new(): static
    {
        return static::create();
    }

    /**
     * Set the endpoint
     *
     * @param string $endpoint
     * @return self
     */
    public function endpoint(string $endpoint): self
    {
        $this->endpoint = trim(trim($endpoint), '/');
        return $this;
    }

    /**
     * Alias of endpoint
     *
     * @param string $endpoint
     * @return self
     */
    public function url(string $endpoint): self
    {
        return $this->endpoint($endpoint);
    }

    /**
     * Set the identifier
     *
     * @param string $id
     * @return self
     */
    public function id(string $id): self
    {
        $this->identifier = $id;
        return $this;
    }

    /**
     * Set the payload
     *
     * @param array|\stdClass $payload
     * @return self
     */
    public function payload(array|\stdClass $payload): self
    {
        $this->payload = $payload;
        return $this;
    }

    /**
     * Alias of payload
     *
     * @param array|\stdClass $body
     * @return self
     */
    public function body(array|\stdClass $body): self
    {
        return $this->payload($body);
    }

    /**
     * Set the secret token
     *
     * @param string $secret
     * @return self
     */
    public function secret(string $secret): self
    {
        $this->secret = $secret;
        return $this;
    }

    /**
     * Set the attempts
     *
     * @param int $tries
     * @return self
     */
    public function tries(int $tries): self
    {
        $this->tries = $tries;
        return $this;
    }

    /**
     * Alias of tries
     *
     * @param int $attempts
     * @return self
     */
    public function attempts(int $attempts): self
    {
        return $this->tries($attempts);
    }

    /**
     * Set the minutes between attempts
     *
     * @param int $backoff
     * @return self
     */
    public function backoff(int $backoff): self
    {
        $this->backoff = $backoff;
        return $this;
    }

    /**
     * Build the pixel
     *
     * @return string
     */
    private function build(): string
    {
        $params = http_build_query([
            'id' => $this->identifier ?: throw new \InvalidArgumentException('Pixel identifier is required.'),
            'p' => Jwt::secret($this->secret)
                    ->payload($this->payload)
                    ->iss(parse_url($this->endpoint, PHP_URL_HOST))
                    ->bwr()
                    ->ip()
                    ->encode(),
        ]);

        return $this->endpoint . '?' . $params;
    }

    /**
     * Add the pixel to queue
     *
     * @return void
     */
    public function queue(): void
    {
        Queue::add(
            $this->identifier,
            $this->build(),
            $this->tries,
            $this->backoff
        );
    }

    /**
     * Alias of queue
     *
     * @return void
     */
    public function dispatch(): void
    {
        $this->queue();
    }

    /**
     * Alias of queue
     *
     * @return void
     */
    public function send(): void
    {
        $this->queue();
    }
}
