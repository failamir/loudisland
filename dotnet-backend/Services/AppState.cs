using LoudIsland.DotNetBackend.Models;

namespace LoudIsland.DotNetBackend.Services;

public sealed class AppState
{
    private readonly object _lock = new();
    private int _userId = 3;
    private int _roleId = 3;
    private int _permissionId = 4;
    private int _eventId = 3;
    private int _ticketId = 3;
    private int _transactionId = 3;
    private int _participantId = 3;
    private int _promoCodeId = 2;
    private int _referralCodeId = 2;
    private int _withdrawalId = 2;
    private int _nomorPunggungId = 4;

    public List<AppUser> Users { get; } =
    [
        new() { Id = 1, Name = "Admin LoudIsland", Email = "admin@loudisland.test", Password = "password", Phone = "081111111111", Roles = ["admin"] },
        new() { Id = 2, Name = "Racepack Staff", Email = "staff@loudisland.test", Password = "password", Phone = "082222222222", Roles = ["staff"] }
    ];

    public List<Permission> Permissions { get; } =
    [
        new() { Id = 1, Title = "users.manage" },
        new() { Id = 2, Title = "events.manage" },
        new() { Id = 3, Title = "transactions.view" }
    ];

    public List<Role> Roles { get; } =
    [
        new() { Id = 1, Title = "admin", PermissionIds = [1, 2, 3] },
        new() { Id = 2, Title = "staff", PermissionIds = [3] }
    ];

    public List<EventItem> Events { get; } =
    [
        new() { Id = 1, Name = "Mandalika Korpri Run 5K", Date = DateTimeOffset.UtcNow.AddDays(30), Location = "Mandalika", IsActive = true },
        new() { Id = 2, Name = "Mandalika Korpri Run 10K", Date = DateTimeOffset.UtcNow.AddDays(30), Location = "Mandalika", IsActive = true }
    ];

    public List<TicketItem> Tickets { get; } =
    [
        new() { Id = 1, EventId = 1, Name = "5K Regular", Price = 150000, Quota = 500 },
        new() { Id = 2, EventId = 2, Name = "10K Regular", Price = 250000, Quota = 300 }
    ];

    public List<TransactionItem> Transactions { get; } =
    [
        new() { Id = 1, Invoice = "TRX-DEMO-001", BuyerName = "Amir", Email = "amir@example.com", Amount = 150000, Status = "success", CreatedAt = DateTimeOffset.UtcNow.AddDays(-2) },
        new() { Id = 2, Invoice = "TRX-DEMO-002", BuyerName = "Nita", Email = "nita@example.com", Amount = 250000, Status = "pending", CreatedAt = DateTimeOffset.UtcNow.AddDays(-1) }
    ];

    public List<ParticipantItem> Participants { get; } =
    [
        new() { Id = 1, TransactionId = 1, Name = "Amir", Email = "amir@example.com", ShirtSize = "L", RacepackCollected = false, BIBNumber = "BIB-001" },
        new() { Id = 2, TransactionId = 2, Name = "Nita", Email = "nita@example.com", ShirtSize = "M", RacepackCollected = true, BIBNumber = "BIB-002" }
    ];

    public List<PromoCodeItem> PromoCodes { get; } =
    [
        new() { Id = 1, Code = "LOUD10", DiscountAmount = 10000, IsActive = true }
    ];

    public List<ReferralCodeItem> ReferralCodes { get; } =
    [
        new() { Id = 1, UserId = 1, Code = "ADMINREF", Status = "active", Balance = 50000 }
    ];

    public List<WithdrawalItem> Withdrawals { get; } =
    [
        new() { Id = 1, UserId = 1, Amount = 25000, Status = "pending", CreatedAt = DateTimeOffset.UtcNow.AddDays(-1) }
    ];

    public List<NomorPunggungItem> NomorPunggung { get; } =
    [
        new() { Id = 1, Nomor = "1001", ParticipantId = 1 },
        new() { Id = 2, Nomor = "1002", ParticipantId = 2 },
        new() { Id = 3, Nomor = "1003", ParticipantId = null }
    ];

    public T Add<T>(ICollection<T> collection, T item, Action<T, int> assignId, Func<int> nextIdFactory)
    {
        lock (_lock)
        {
            assignId(item, nextIdFactory());
            collection.Add(item);
            return item;
        }
    }

    public int NextUserId() => _userId++;
    public int NextRoleId() => _roleId++;
    public int NextPermissionId() => _permissionId++;
    public int NextEventId() => _eventId++;
    public int NextTicketId() => _ticketId++;
    public int NextTransactionId() => _transactionId++;
    public int NextParticipantId() => _participantId++;
    public int NextPromoCodeId() => _promoCodeId++;
    public int NextReferralCodeId() => _referralCodeId++;
    public int NextWithdrawalId() => _withdrawalId++;
    public int NextNomorPunggungId() => _nomorPunggungId++;
}
