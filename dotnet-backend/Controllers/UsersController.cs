using LoudIsland.DotNetBackend.Contracts;
using LoudIsland.DotNetBackend.Models;
using LoudIsland.DotNetBackend.Services;
using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;

namespace LoudIsland.DotNetBackend.Controllers;

[Authorize]
[Route("api/v1/users")]
public sealed class UsersController(AppState state) : BaseApiController(state)
{
    [HttpGet]
    public IActionResult Index() => Ok(State.Users);

    [HttpGet("{id:int}")]
    public IActionResult Show(int id)
    {
        var user = State.Users.FirstOrDefault(item => item.Id == id);
        return user is null ? NotFound() : Ok(user);
    }

    [HttpPost]
    public IActionResult Store(UserUpsertRequest request)
    {
        var user = State.Add(State.Users, new AppUser
        {
            Name = request.Name,
            Email = request.Email,
            Password = request.Password,
            Phone = request.Phone ?? string.Empty,
            Roles = request.Roles ?? ["staff"]
        }, (item, id) => item.Id = id, State.NextUserId);

        return CreatedAtAction(nameof(Show), new { id = user.Id }, user);
    }

    [HttpPut("{id:int}")]
    public IActionResult Update(int id, UserUpsertRequest request)
    {
        var user = State.Users.FirstOrDefault(item => item.Id == id);
        if (user is null)
        {
            return NotFound();
        }

        user.Name = request.Name;
        user.Email = request.Email;
        user.Password = request.Password;
        user.Phone = request.Phone ?? string.Empty;
        user.Roles = request.Roles ?? user.Roles;
        return Ok(user);
    }

    [HttpDelete("{id:int}")]
    public IActionResult Destroy(int id)
    {
        var user = State.Users.FirstOrDefault(item => item.Id == id);
        if (user is null)
        {
            return NotFound();
        }

        State.Users.Remove(user);
        return NoContent();
    }

    [HttpPost("/api/v1/admin/users/{id:int}/revoke-tokens")]
    public IActionResult RevokeTokens(int id)
    {
        return State.Users.Any(item => item.Id == id)
            ? Ok(new { message = "Token revocation simulated." })
            : NotFound();
    }
}
