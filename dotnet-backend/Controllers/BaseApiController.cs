using System.Security.Claims;
using LoudIsland.DotNetBackend.Models;
using LoudIsland.DotNetBackend.Services;
using Microsoft.AspNetCore.Mvc;

namespace LoudIsland.DotNetBackend.Controllers;

[ApiController]
public abstract class BaseApiController(AppState state) : ControllerBase
{
    protected AppState State => state;

    protected AppUser? CurrentUser
    {
        get
        {
            var idValue = User.FindFirstValue(ClaimTypes.NameIdentifier);
            return int.TryParse(idValue, out var id) ? State.Users.FirstOrDefault(user => user.Id == id) : null;
        }
    }
}
