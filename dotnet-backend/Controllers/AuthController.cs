using LoudIsland.DotNetBackend.Contracts;
using LoudIsland.DotNetBackend.Models;
using LoudIsland.DotNetBackend.Services;
using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;

namespace LoudIsland.DotNetBackend.Controllers;

[Route("api/v1")]
public sealed class AuthController(AppState state, JwtTokenService jwtTokenService) : BaseApiController(state)
{
    [HttpPost("register")]
    public ActionResult<AuthResponse> Register(RegisterRequest request)
    {
        if (State.Users.Any(user => string.Equals(user.Email, request.Email, StringComparison.OrdinalIgnoreCase)))
        {
            return Conflict(new { message = "Email already exists." });
        }

        var user = State.Add(State.Users, new AppUser
        {
            Name = request.Name,
            Email = request.Email,
            Password = request.Password,
            Phone = request.Phone ?? string.Empty,
            Roles = ["staff"]
        }, (item, id) => item.Id = id, State.NextUserId);

        var (token, expiresIn) = jwtTokenService.CreateToken(user);
        return Created(string.Empty, new AuthResponse(token, "bearer", expiresIn, ToUserDto(user)));
    }

    [HttpPost("login")]
    public ActionResult<AuthResponse> Login(LoginRequest request)
    {
        var user = State.Users.FirstOrDefault(candidate =>
            string.Equals(candidate.Email, request.Email, StringComparison.OrdinalIgnoreCase) &&
            candidate.Password == request.Password);

        if (user is null)
        {
            return Unauthorized(new { message = "Invalid credentials." });
        }

        var (token, expiresIn) = jwtTokenService.CreateToken(user);
        return Ok(new AuthResponse(token, "bearer", expiresIn, ToUserDto(user)));
    }

    [Authorize]
    [HttpPost("refresh")]
    public ActionResult<AuthResponse> Refresh()
    {
        var user = CurrentUser;
        if (user is null)
        {
            return Unauthorized();
        }

        var (token, expiresIn) = jwtTokenService.CreateToken(user);
        return Ok(new AuthResponse(token, "bearer", expiresIn, ToUserDto(user)));
    }

    [Authorize]
    [HttpGet("me")]
    public ActionResult<object> Me()
    {
        return CurrentUser is null ? Unauthorized() : Ok(ToUserDto(CurrentUser));
    }

    [Authorize]
    [HttpPost("logout")]
    public IActionResult Logout()
    {
        return Ok(new { message = "Logout handled client-side for stateless JWT." });
    }

    [HttpPost("password/forgot")]
    public IActionResult ForgotPassword(ForgotPasswordRequest request)
    {
        return Ok(new { message = $"Password reset link simulated for {request.Email}." });
    }

    [HttpPost("password/reset")]
    public IActionResult ResetPassword(ResetPasswordRequest request)
    {
        var user = State.Users.FirstOrDefault(candidate =>
            string.Equals(candidate.Email, request.Email, StringComparison.OrdinalIgnoreCase));

        if (user is null)
        {
            return NotFound(new { message = "User not found." });
        }

        user.Password = request.Password;
        return Ok(new { message = "Password updated." });
    }

    private static object ToUserDto(AppUser user) => new
    {
        user.Id,
        user.Name,
        user.Email,
        user.Phone,
        roles = user.Roles
    };
}
