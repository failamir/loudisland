namespace LoudIsland.DotNetBackend.Models;

public sealed class AppUser
{
    public int Id { get; set; }
    public string Name { get; set; } = string.Empty;
    public string Email { get; set; } = string.Empty;
    public string Password { get; set; } = string.Empty;
    public string Phone { get; set; } = string.Empty;
    public List<string> Roles { get; set; } = [];
}

public sealed class Permission
{
    public int Id { get; set; }
    public string Title { get; set; } = string.Empty;
}

public sealed class Role
{
    public int Id { get; set; }
    public string Title { get; set; } = string.Empty;
    public List<int> PermissionIds { get; set; } = [];
}

public sealed class EventItem
{
    public int Id { get; set; }
    public string Name { get; set; } = string.Empty;
    public DateTimeOffset Date { get; set; }
    public string Location { get; set; } = string.Empty;
    public bool IsActive { get; set; }
}

public sealed class TicketItem
{
    public int Id { get; set; }
    public int EventId { get; set; }
    public string Name { get; set; } = string.Empty;
    public decimal Price { get; set; }
    public int Quota { get; set; }
}

public sealed class TransactionItem
{
    public int Id { get; set; }
    public string Invoice { get; set; } = string.Empty;
    public string BuyerName { get; set; } = string.Empty;
    public string Email { get; set; } = string.Empty;
    public decimal Amount { get; set; }
    public string Status { get; set; } = "pending";
    public DateTimeOffset CreatedAt { get; set; }
}

public sealed class ParticipantItem
{
    public int Id { get; set; }
    public int TransactionId { get; set; }
    public string Name { get; set; } = string.Empty;
    public string Email { get; set; } = string.Empty;
    public string ShirtSize { get; set; } = "M";
    public bool RacepackCollected { get; set; }
    public string BIBNumber { get; set; } = string.Empty;
}

public sealed class PromoCodeItem
{
    public int Id { get; set; }
    public string Code { get; set; } = string.Empty;
    public decimal DiscountAmount { get; set; }
    public bool IsActive { get; set; }
}

public sealed class ReferralCodeItem
{
    public int Id { get; set; }
    public int UserId { get; set; }
    public string Code { get; set; } = string.Empty;
    public string Status { get; set; } = "active";
    public decimal Balance { get; set; }
}

public sealed class WithdrawalItem
{
    public int Id { get; set; }
    public int UserId { get; set; }
    public decimal Amount { get; set; }
    public string Status { get; set; } = "pending";
    public DateTimeOffset CreatedAt { get; set; }
}

public sealed class NomorPunggungItem
{
    public int Id { get; set; }
    public string Nomor { get; set; } = string.Empty;
    public int? ParticipantId { get; set; }
}
