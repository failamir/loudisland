namespace LoudIsland.DotNetBackend.Contracts;

public sealed record AuthResponse(string AccessToken, string TokenType, int ExpiresIn, object User);
