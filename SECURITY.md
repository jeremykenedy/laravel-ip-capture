# Security Policy

## Supported versions

| Version | Supported |
|---------|-----------|
| 1.x | Yes |

## Reporting a vulnerability

Please report security issues privately rather than opening a public issue. Use
GitHub's [private vulnerability reporting](https://github.com/jeremykenedy/laravel-ip-capture/security/advisories/new)
or email jeremykenedy@gmail.com.

Include the package version, the Laravel and PHP versions, and the steps to
reproduce. You will get an acknowledgement within a few days.

## Notes on stored addresses

An IP address is personal data in several jurisdictions. Two options help:

- `IP_CAPTURE_HASH=true` with `IP_CAPTURE_HASH_SALT` set stores a one way digest
  rather than the address. Set the salt before you start storing digests. Without
  a salt, the whole IPv4 space can be hashed and compared.
- `IP_CAPTURE_ANONYMIZE=true` keeps only the network part of the address, a /24
  for IPv4 and a /64 for IPv6, and runs before hashing.

The proxy header list in `config('ip-capture.headers')` is only as trustworthy as
the proxy in front of the application. Any header in that list can be set by a
client talking to the application directly, so remove the ones your
infrastructure does not set.
