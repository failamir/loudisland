using LoudIsland.DotNetBackend.Contracts;
using LoudIsland.DotNetBackend.Models;
using LoudIsland.DotNetBackend.Services;
using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;

namespace LoudIsland.DotNetBackend.Controllers;

[Authorize]
[Route("api/v1/roles")]
public sealed class RolesController(AppState state) : BaseApiController(state)
{
    [HttpGet]
    public IActionResult Index() => Ok(State.Roles);

    [HttpGet("{id:int}")]
    public IActionResult Show(int id) => State.Roles.FirstOrDefault(item => item.Id == id) is { } role ? Ok(role) : NotFound();

    [HttpPost]
    public IActionResult Store(RoleUpsertRequest request)
    {
        var role = State.Add(State.Roles, new Role
        {
            Title = request.Title,
            PermissionIds = request.PermissionIds ?? []
        }, (item, id) => item.Id = id, State.NextRoleId);

        return CreatedAtAction(nameof(Show), new { id = role.Id }, role);
    }

    [HttpPut("{id:int}")]
    public IActionResult Update(int id, RoleUpsertRequest request)
    {
        var role = State.Roles.FirstOrDefault(item => item.Id == id);
        if (role is null)
        {
            return NotFound();
        }

        role.Title = request.Title;
        role.PermissionIds = request.PermissionIds ?? [];
        return Ok(role);
    }

    [HttpDelete("{id:int}")]
    public IActionResult Destroy(int id)
    {
        var role = State.Roles.FirstOrDefault(item => item.Id == id);
        if (role is null)
        {
            return NotFound();
        }

        State.Roles.Remove(role);
        return NoContent();
    }
}

[Authorize]
[Route("api/v1/permissions")]
public sealed class PermissionsController(AppState state) : BaseApiController(state)
{
    [HttpGet]
    public IActionResult Index() => Ok(State.Permissions);

    [HttpGet("{id:int}")]
    public IActionResult Show(int id) => State.Permissions.FirstOrDefault(item => item.Id == id) is { } permission ? Ok(permission) : NotFound();

    [HttpPost]
    public IActionResult Store(PermissionUpsertRequest request)
    {
        var permission = State.Add(State.Permissions, new Permission
        {
            Title = request.Title
        }, (item, id) => item.Id = id, State.NextPermissionId);

        return CreatedAtAction(nameof(Show), new { id = permission.Id }, permission);
    }

    [HttpPut("{id:int}")]
    public IActionResult Update(int id, PermissionUpsertRequest request)
    {
        var permission = State.Permissions.FirstOrDefault(item => item.Id == id);
        if (permission is null)
        {
            return NotFound();
        }

        permission.Title = request.Title;
        return Ok(permission);
    }

    [HttpDelete("{id:int}")]
    public IActionResult Destroy(int id)
    {
        var permission = State.Permissions.FirstOrDefault(item => item.Id == id);
        if (permission is null)
        {
            return NotFound();
        }

        State.Permissions.Remove(permission);
        return NoContent();
    }
}

[Authorize]
[Route("api/v1/events")]
public sealed class EventsController(AppState state) : BaseApiController(state)
{
    [HttpGet]
    public IActionResult Index() => Ok(State.Events);

    [HttpGet("{id:int}")]
    public IActionResult Show(int id) => State.Events.FirstOrDefault(item => item.Id == id) is { } eventItem ? Ok(eventItem) : NotFound();

    [HttpPost]
    public IActionResult Store(EventUpsertRequest request)
    {
        var eventItem = State.Add(State.Events, new EventItem
        {
            Name = request.Name,
            Date = request.Date,
            Location = request.Location,
            IsActive = request.IsActive
        }, (item, id) => item.Id = id, State.NextEventId);

        return CreatedAtAction(nameof(Show), new { id = eventItem.Id }, eventItem);
    }

    [HttpPut("{id:int}")]
    public IActionResult Update(int id, EventUpsertRequest request)
    {
        var eventItem = State.Events.FirstOrDefault(item => item.Id == id);
        if (eventItem is null)
        {
            return NotFound();
        }

        eventItem.Name = request.Name;
        eventItem.Date = request.Date;
        eventItem.Location = request.Location;
        eventItem.IsActive = request.IsActive;
        return Ok(eventItem);
    }

    [HttpDelete("{id:int}")]
    public IActionResult Destroy(int id)
    {
        var eventItem = State.Events.FirstOrDefault(item => item.Id == id);
        if (eventItem is null)
        {
            return NotFound();
        }

        State.Events.Remove(eventItem);
        return NoContent();
    }
}

[Authorize]
[Route("api/v1/tikets")]
public sealed class TicketsController(AppState state) : BaseApiController(state)
{
    [HttpGet]
    public IActionResult Index() => Ok(State.Tickets);

    [HttpGet("{id:int}")]
    public IActionResult Show(int id) => State.Tickets.FirstOrDefault(item => item.Id == id) is { } ticket ? Ok(ticket) : NotFound();

    [HttpPost]
    public IActionResult Store(TicketUpsertRequest request)
    {
        var ticket = State.Add(State.Tickets, new TicketItem
        {
            EventId = request.EventId,
            Name = request.Name,
            Price = request.Price,
            Quota = request.Quota
        }, (item, id) => item.Id = id, State.NextTicketId);

        return CreatedAtAction(nameof(Show), new { id = ticket.Id }, ticket);
    }

    [HttpPut("{id:int}")]
    public IActionResult Update(int id, TicketUpsertRequest request)
    {
        var ticket = State.Tickets.FirstOrDefault(item => item.Id == id);
        if (ticket is null)
        {
            return NotFound();
        }

        ticket.EventId = request.EventId;
        ticket.Name = request.Name;
        ticket.Price = request.Price;
        ticket.Quota = request.Quota;
        return Ok(ticket);
    }

    [HttpDelete("{id:int}")]
    public IActionResult Destroy(int id)
    {
        var ticket = State.Tickets.FirstOrDefault(item => item.Id == id);
        if (ticket is null)
        {
            return NotFound();
        }

        State.Tickets.Remove(ticket);
        return NoContent();
    }
}
