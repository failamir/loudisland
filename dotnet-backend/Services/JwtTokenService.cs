using System.IdentityModel.Tokens.Jwt;
using System.Security.Claims;
using System.Text;
using LoudIsland.DotNetBackend.Models;
using Microsoft.IdentityModel.Tokens;

namespace LoudIsland.DotNetBackend.Services;

public sealed class JwtTokenService(IConfiguration configuration)
{
    public (string Token, int ExpiresIn) CreateToken(AppUser user)
    {
        var expiresMinutes = configuration.GetValue<int?>("Jwt:ExpiresMinutes") ?? 120;
        var expires = DateTime.UtcNow.AddMinutes(expiresMinutes);
        var key = new SymmetricSecurityKey(Encoding.UTF8.GetBytes(configuration["Jwt:Key"] ?? "development-key-change-me"));
        var credentials = new SigningCredentials(key, SecurityAlgorithms.HmacSha256);
        var claims = new List<Claim>
        {
            new(JwtRegisteredClaimNames.Sub, user.Id.ToString()),
            new(JwtRegisteredClaimNames.Email, user.Email),
            new(JwtRegisteredClaimNames.Name, user.Name),
            new(ClaimTypes.NameIdentifier, user.Id.ToString()),
            new(ClaimTypes.Name, user.Name),
            new(ClaimTypes.Email, user.Email)
        };

        claims.AddRange(user.Roles.Select(role => new Claim(ClaimTypes.Role, role)));

        var token = new JwtSecurityToken(
            issuer: configuration["Jwt:Issuer"],
            audience: configuration["Jwt:Audience"],
            claims: claims,
            expires: expires,
            signingCredentials: credentials);

        return (new JwtSecurityTokenHandler().WriteToken(token), expiresMinutes * 60);
    }
}
