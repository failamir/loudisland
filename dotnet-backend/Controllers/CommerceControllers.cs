using LoudIsland.DotNetBackend.Contracts;
using LoudIsland.DotNetBackend.Models;
using LoudIsland.DotNetBackend.Services;
using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;

namespace LoudIsland.DotNetBackend.Controllers;

[Route("api/v1/transactions")]
public sealed class TransactionsController(AppState state) : BaseApiController(state)
{
    [Authorize]
    [HttpGet]
    public IActionResult Index([FromQuery] string? status)
    {
        var items = string.IsNullOrWhiteSpace(status)
            ? State.Transactions
            : State.Transactions.Where(item => string.Equals(item.Status, status, StringComparison.OrdinalIgnoreCase)).ToList();

        return Ok(items);
    }

    [Authorize]
    [HttpGet("simple")]
    public IActionResult Simple([FromQuery] string? status) => Index(status);

    [Authorize]
    [HttpGet("show")]
    public IActionResult ShowByQuery([FromQuery] int? id, [FromQuery] string? invoice)
    {
        var item = id.HasValue
            ? State.Transactions.FirstOrDefault(transaction => transaction.Id == id.Value)
            : State.Transactions.FirstOrDefault(transaction => string.Equals(transaction.Invoice, invoice, StringComparison.OrdinalIgnoreCase));

        return item is null ? NotFound() : Ok(item);
    }

    [Authorize]
    [HttpGet("{id:int}")]
    public IActionResult Show(int id) => State.Transactions.FirstOrDefault(item => item.Id == id) is { } transaction ? Ok(transaction) : NotFound();

    [Authorize]
    [HttpPost]
    public IActionResult Store(TransactionUpsertRequest request)
    {
        var transaction = State.Add(State.Transactions, new TransactionItem
        {
            Invoice = $"TRX-{Guid.NewGuid():N}"[..12].ToUpperInvariant(),
            BuyerName = request.BuyerName,
            Email = request.Email,
            Amount = request.Amount,
            Status = request.Status,
            CreatedAt = DateTimeOffset.UtcNow
        }, (item, id) => item.Id = id, State.NextTransactionId);

        return CreatedAtAction(nameof(Show), new { id = transaction.Id }, transaction);
    }

    [Authorize]
    [HttpPut("{id:int}")]
    public IActionResult Update(int id, TransactionUpsertRequest request)
    {
        var transaction = State.Transactions.FirstOrDefault(item => item.Id == id);
        if (transaction is null)
        {
            return NotFound();
        }

        transaction.BuyerName = request.BuyerName;
        transaction.Email = request.Email;
        transaction.Amount = request.Amount;
        transaction.Status = request.Status;
        return Ok(transaction);
    }

    [Authorize]
    [HttpDelete("{id:int}")]
    public IActionResult Destroy(int id)
    {
        var transaction = State.Transactions.FirstOrDefault(item => item.Id == id);
        if (transaction is null)
        {
            return NotFound();
        }

        State.Transactions.Remove(transaction);
        return NoContent();
    }

    [HttpGet("/api/v1/payment/{invoice}")]
    public IActionResult PaymentStatus(string invoice)
    {
        var transaction = State.Transactions.FirstOrDefault(item => string.Equals(item.Invoice, invoice, StringComparison.OrdinalIgnoreCase));
        return transaction is null ? NotFound() : Ok(new { transaction.Invoice, transaction.Status, transaction.Amount });
    }
}

[Route("api/v1/participants")]
public sealed class ParticipantsController(AppState state) : BaseApiController(state)
{
    [Authorize]
    [HttpGet]
    public IActionResult Index() => Ok(State.Participants);

    [HttpGet("{id:int}")]
    public IActionResult Show(int id) => State.Participants.FirstOrDefault(item => item.Id == id) is { } participant ? Ok(participant) : NotFound();

    [Authorize]
    [HttpGet("missing")]
    public IActionResult Missing()
    {
        var transactionIds = State.Participants.Select(item => item.TransactionId).ToHashSet();
        var missing = State.Transactions.Where(item => item.Status == "success" && !transactionIds.Contains(item.Id)).ToList();
        return Ok(missing);
    }

    [Authorize]
    [HttpPost("generate")]
    public IActionResult Generate([FromBody] Dictionary<string, string> request)
    {
        if (!request.TryGetValue("invoice", out var invoice))
        {
            return BadRequest(new { message = "invoice is required." });
        }

        var transaction = State.Transactions.FirstOrDefault(item => string.Equals(item.Invoice, invoice, StringComparison.OrdinalIgnoreCase));
        if (transaction is null)
        {
            return NotFound();
        }

        var participantId = State.NextParticipantId();
        var participant = new ParticipantItem
        {
            Id = participantId,
            TransactionId = transaction.Id,
            Name = transaction.BuyerName,
            Email = transaction.Email,
            ShirtSize = "M",
            RacepackCollected = false,
            BIBNumber = $"BIB-{participantId:000}"
        };

        State.Participants.Add(participant);
        return Ok(participant);
    }

    [Authorize]
    [HttpPut("{id:int}/shirt-size")]
    public IActionResult UpdateShirtSize(int id, ShirtSizeUpdateRequest request)
    {
        var participant = State.Participants.FirstOrDefault(item => item.Id == id);
        if (participant is null)
        {
            return NotFound();
        }

        participant.ShirtSize = request.ShirtSize;
        return Ok(participant);
    }

    [Authorize]
    [HttpPost("bulk-update-shirt-size")]
    public IActionResult BulkUpdateShirtSize(BulkShirtSizeUpdateRequest request)
    {
        var updated = State.Participants
            .Where(item => request.ParticipantIds.Contains(item.Id))
            .Select(item =>
            {
                item.ShirtSize = request.ShirtSize;
                return item;
            })
            .ToList();

        return Ok(updated);
    }

    [Authorize]
    [HttpPost]
    public IActionResult Store(ParticipantUpsertRequest request)
    {
        var participant = State.Add(State.Participants, new ParticipantItem
        {
            TransactionId = request.TransactionId,
            Name = request.Name,
            Email = request.Email,
            ShirtSize = request.ShirtSize,
            RacepackCollected = request.RacepackCollected,
            BIBNumber = request.BIBNumber ?? string.Empty
        }, (item, id) => item.Id = id, State.NextParticipantId);

        return CreatedAtAction(nameof(Show), new { id = participant.Id }, participant);
    }
}

[Authorize]
[Route("api/v1/promo-codes")]
public sealed class PromoCodesController(AppState state) : BaseApiController(state)
{
    [HttpGet]
    public IActionResult Index() => Ok(State.PromoCodes);

    [HttpPost]
    public IActionResult Store(PromoCodeUpsertRequest request)
    {
        var code = State.Add(State.PromoCodes, new PromoCodeItem
        {
            Code = request.Code,
            DiscountAmount = request.DiscountAmount,
            IsActive = request.IsActive
        }, (item, id) => item.Id = id, State.NextPromoCodeId);

        return CreatedAtAction(nameof(Index), new { id = code.Id }, code);
    }

    [HttpPut("{id:int}")]
    public IActionResult Update(int id, PromoCodeUpsertRequest request)
    {
        var code = State.PromoCodes.FirstOrDefault(item => item.Id == id);
        if (code is null)
        {
            return NotFound();
        }

        code.Code = request.Code;
        code.DiscountAmount = request.DiscountAmount;
        code.IsActive = request.IsActive;
        return Ok(code);
    }

    [HttpDelete("{id:int}")]
    public IActionResult Destroy(int id)
    {
        var code = State.PromoCodes.FirstOrDefault(item => item.Id == id);
        if (code is null)
        {
            return NotFound();
        }

        State.PromoCodes.Remove(code);
        return NoContent();
    }

    [AllowAnonymous]
    [HttpPost("/api/v1/promo-codes/validate")]
    public IActionResult ValidateCode([FromBody] Dictionary<string, string> request)
    {
        var code = request.GetValueOrDefault("code");
        var item = State.PromoCodes.FirstOrDefault(promo => string.Equals(promo.Code, code, StringComparison.OrdinalIgnoreCase) && promo.IsActive);
        return item is null ? NotFound(new { message = "Promo code not found." }) : Ok(item);
    }

    [AllowAnonymous]
    [HttpPost("/api/v1/promo-codes/redeem")]
    public IActionResult Redeem([FromBody] Dictionary<string, string> request) => ValidateCode(request);
}
