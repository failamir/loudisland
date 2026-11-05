using LoudIsland.DotNetBackend.Contracts;
using LoudIsland.DotNetBackend.Models;
using LoudIsland.DotNetBackend.Services;
using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;

namespace LoudIsland.DotNetBackend.Controllers;

[Authorize]
[Route("api/v1/racepacks")]
public sealed class RacepacksController(AppState state) : BaseApiController(state)
{
    [HttpGet]
    public IActionResult Index()
    {
        var items = from participant in State.Participants
                    join transaction in State.Transactions on participant.TransactionId equals transaction.Id
                    select new
                    {
                        participant.Id,
                        participant.Name,
                        participant.Email,
                        participant.ShirtSize,
                        participant.RacepackCollected,
                        participant.BIBNumber,
                        transaction.Invoice,
                        transaction.Status
                    };

        return Ok(items);
    }

    [HttpGet("/api/v1/staffs")]
    public IActionResult Staffs()
    {
        var staffs = State.Users.Where(user => user.Roles.Contains("staff")).ToList();
        return Ok(staffs);
    }
}

[Authorize]
[Route("api/v1/nomor-punggung")]
public sealed class NomorPunggungController(AppState state) : BaseApiController(state)
{
    [HttpGet]
    public IActionResult Index() => Ok(State.NomorPunggung);

    [HttpPost("generate")]
    public IActionResult Generate(NomorPunggungGenerateRequest request)
    {
        var count = Math.Max(1, request.Count);
        var generated = new List<NomorPunggungItem>();

        for (var i = 0; i < count; i++)
        {
            var nextNumber = 1000 + State.NomorPunggung.Count + 1;
            var item = State.Add(State.NomorPunggung, new NomorPunggungItem
            {
                Nomor = nextNumber.ToString(),
                ParticipantId = null
            }, (nomor, id) => nomor.Id = id, State.NextNomorPunggungId);

            generated.Add(item);
        }

        return Ok(generated);
    }

    [HttpPost("pair")]
    public IActionResult Pair(NomorPunggungPairRequest request)
    {
        var item = State.NomorPunggung.FirstOrDefault(nomor => nomor.Nomor == request.Nomor);
        if (item is null)
        {
            return NotFound();
        }

        item.ParticipantId = request.ParticipantId;
        return Ok(item);
    }

    [HttpPost("unpair")]
    public IActionResult Unpair([FromBody] Dictionary<string, string> request)
    {
        var nomor = request.GetValueOrDefault("nomor");
        var item = State.NomorPunggung.FirstOrDefault(candidate => candidate.Nomor == nomor);
        if (item is null)
        {
            return NotFound();
        }

        item.ParticipantId = null;
        return Ok(item);
    }
}

[Authorize]
[Route("api/v1/withdrawals")]
public sealed class WithdrawalsController(AppState state) : BaseApiController(state)
{
    [HttpGet]
    public IActionResult Index() => Ok(State.Withdrawals);

    [HttpGet("summary")]
    public IActionResult Summary()
    {
        var totalRequested = State.Withdrawals.Sum(item => item.Amount);
        var totalPending = State.Withdrawals.Where(item => item.Status == "pending").Sum(item => item.Amount);
        return Ok(new { totalRequested, totalPending });
    }

    [HttpGet("{id:int}")]
    public IActionResult Show(int id) => State.Withdrawals.FirstOrDefault(item => item.Id == id) is { } withdrawal ? Ok(withdrawal) : NotFound();

    [HttpPost]
    public IActionResult Store(WithdrawalRequest request)
    {
        var user = CurrentUser;
        if (user is null)
        {
            return Unauthorized();
        }

        var withdrawal = State.Add(State.Withdrawals, new WithdrawalItem
        {
            UserId = user.Id,
            Amount = request.Amount,
            Status = "pending",
            CreatedAt = DateTimeOffset.UtcNow
        }, (item, id) => item.Id = id, State.NextWithdrawalId);

        return CreatedAtAction(nameof(Show), new { id = withdrawal.Id }, withdrawal);
    }

    [HttpPatch("{id:int}/status")]
    public IActionResult UpdateStatus(int id, WithdrawalStatusUpdateRequest request)
    {
        var withdrawal = State.Withdrawals.FirstOrDefault(item => item.Id == id);
        if (withdrawal is null)
        {
            return NotFound();
        }

        withdrawal.Status = request.Status;
        return Ok(withdrawal);
    }
}

[Authorize]
[Route("api/v1/referral-codes")]
public sealed class ReferralCodesController(AppState state) : BaseApiController(state)
{
    [HttpGet]
    public IActionResult Index() => Ok(State.ReferralCodes);

    [HttpPost]
    public IActionResult Store([FromBody] Dictionary<string, string> request)
    {
        var user = CurrentUser;
        if (user is null)
        {
            return Unauthorized();
        }

        var codeValue = request.GetValueOrDefault("code") ?? $"REF{Guid.NewGuid():N}"[..8].ToUpperInvariant();
        var code = State.Add(State.ReferralCodes, new ReferralCodeItem
        {
            UserId = user.Id,
            Code = codeValue,
            Status = "pending",
            Balance = 0
        }, (item, id) => item.Id = id, State.NextReferralCodeId);

        return CreatedAtAction(nameof(Index), new { id = code.Id }, code);
    }

    [HttpPatch("{id:int}")]
    public IActionResult Update(int id, [FromBody] Dictionary<string, string> request)
    {
        var code = State.ReferralCodes.FirstOrDefault(item => item.Id == id);
        if (code is null)
        {
            return NotFound();
        }

        code.Code = request.GetValueOrDefault("code") ?? code.Code;
        return Ok(code);
    }

    [HttpDelete("{id:int}")]
    public IActionResult Destroy(int id)
    {
        var code = State.ReferralCodes.FirstOrDefault(item => item.Id == id);
        if (code is null)
        {
            return NotFound();
        }

        State.ReferralCodes.Remove(code);
        return NoContent();
    }

    [HttpGet("mine")]
    public IActionResult Mine()
    {
        var user = CurrentUser;
        if (user is null)
        {
            return Unauthorized();
        }

        var mine = State.ReferralCodes.Where(item => item.UserId == user.Id).ToList();
        return Ok(mine);
    }

    [HttpPatch("mine")]
    public IActionResult UpdateMine([FromBody] Dictionary<string, string> request)
    {
        var user = CurrentUser;
        if (user is null)
        {
            return Unauthorized();
        }

        var code = State.ReferralCodes.FirstOrDefault(item => item.UserId == user.Id);
        if (code is null)
        {
            return NotFound();
        }

        code.Code = request.GetValueOrDefault("code") ?? code.Code;
        return Ok(code);
    }

    [HttpGet("transactions")]
    public IActionResult Transactions()
    {
        return Ok(State.Transactions.Where(item => item.Status == "success").ToList());
    }

    [HttpGet("/api/v1/admin/referral-codes")]
    public IActionResult AdminIndex() => Ok(State.ReferralCodes);

    [HttpPatch("/api/v1/admin/referral-codes/{id:int}/status")]
    public IActionResult AdminUpdateStatus(int id, ReferralStatusUpdateRequest request)
    {
        var code = State.ReferralCodes.FirstOrDefault(item => item.Id == id);
        if (code is null)
        {
            return NotFound();
        }

        code.Status = request.Status;
        return Ok(code);
    }

    [AllowAnonymous]
    [HttpPost("/api/v1/referral-codes/validate")]
    public IActionResult ValidatePublic([FromBody] Dictionary<string, string> request)
    {
        var code = request.GetValueOrDefault("code");
        var item = State.ReferralCodes.FirstOrDefault(referral => string.Equals(referral.Code, code, StringComparison.OrdinalIgnoreCase));
        return item is null ? NotFound(new { message = "Referral code not found." }) : Ok(item);
    }
}

[Authorize]
[Route("api/v1/referral")]
public sealed class ReferralWithdrawalsController(AppState state) : BaseApiController(state)
{
    [HttpGet("balance")]
    public IActionResult Balance()
    {
        var user = CurrentUser;
        if (user is null)
        {
            return Unauthorized();
        }

        var balance = State.ReferralCodes.Where(item => item.UserId == user.Id).Sum(item => item.Balance);
        return Ok(new { balance });
    }

    [HttpGet("withdrawals")]
    public IActionResult Index() => Ok(State.Withdrawals);

    [HttpPost("withdrawals")]
    public IActionResult Store(WithdrawalRequest request) => Ok(new { message = "Referral withdrawal request accepted.", request.Amount });
}

[Route("api/v1")]
public sealed class MiscController(AppState state) : BaseApiController(state)
{
    [HttpGet("total-income")]
    public IActionResult TotalIncome() => Ok(new { total = State.Transactions.Where(item => item.Status == "success").Sum(item => item.Amount) });

    [HttpGet("total-final-income-participants")]
    public IActionResult TotalFinalIncomeParticipants() => Ok(new { total = State.Participants.Count });

    [HttpGet("wilayah/provinces")]
    public IActionResult Provinces() => Ok(new[] { new { code = "52", name = "Nusa Tenggara Barat" } });

    [HttpGet("wilayah/regencies/{provinceCode}")]
    public IActionResult Regencies(string provinceCode) => Ok(new[] { new { code = $"{provinceCode}.01", name = "Lombok Barat" } });

    [HttpGet("wilayah/districts/{regencyCode}")]
    public IActionResult Districts(string regencyCode) => Ok(new[] { new { code = $"{regencyCode}.001", name = "Sekotong" } });

    [HttpGet("wilayah/villages/{districtCode}")]
    public IActionResult Villages(string districtCode) => Ok(new[] { new { code = $"{districtCode}.0001", name = "Buwun Mas" } });

    [HttpPost("buy")]
    public IActionResult Buy([FromBody] Dictionary<string, object> payload) => Ok(new { message = "Order accepted.", checkout_url = "https://example.test/checkout/demo", payload });

    [HttpPost("register-ticket")]
    public IActionResult RegisterTicket([FromBody] Dictionary<string, object> payload) => Ok(new { message = "Registration accepted.", checkout_url = "https://example.test/checkout/register", payload });

    [HttpPost("myorder")]
    public IActionResult MyOrder() => Ok(State.Transactions);

    [HttpPost("myticket")]
    public IActionResult MyTicket() => Ok(State.Tickets);

    [HttpPost("daftar")]
    public IActionResult Daftar([FromBody] Dictionary<string, object> payload) => Ok(new { message = "Registration saved.", payload });

    [HttpGet("profile")]
    public IActionResult Profile() => Ok(new { name = "Demo Participant", email = "participant@example.com" });

    [HttpPost("updateprofile")]
    public IActionResult UpdateProfile([FromBody] Dictionary<string, object> payload) => Ok(new { message = "Profile updated.", payload });

    [HttpGet("transaksi")]
    public IActionResult Transaksi() => Ok(State.Transactions);

    [HttpGet("tiket")]
    public IActionResult Tiket() => Ok(State.Tickets);

    [HttpPost("notification")]
    public IActionResult Notification([FromBody] Dictionary<string, object> payload) => Ok(new { message = "Notification received.", payload });

    [HttpPost("scan")]
    public IActionResult Scan([FromBody] Dictionary<string, object> payload) => Ok(new { message = "Scan accepted.", payload });

    [Authorize]
    [HttpPost("scan/start")]
    public IActionResult ScanStart([FromBody] Dictionary<string, object> payload) => Ok(new { message = "Start recorded.", payload });

    [Authorize]
    [HttpPost("scan/finish")]
    public IActionResult ScanFinish([FromBody] Dictionary<string, object> payload) => Ok(new { message = "Finish recorded.", payload });

    [Authorize]
    [HttpGet("pairings")]
    public IActionResult Pairings() => Ok(State.NomorPunggung.Where(item => item.ParticipantId.HasValue).ToList());

    [HttpPost("checkin1")]
    public IActionResult Checkin1() => Ok(new { message = "Check-in 1 recorded." });

    [HttpPost("checkin2")]
    public IActionResult Checkin2() => Ok(new { message = "Check-in 2 recorded." });

    [HttpPost("checkout")]
    public IActionResult Checkout() => Ok(new { message = "Checkout recorded." });
}
