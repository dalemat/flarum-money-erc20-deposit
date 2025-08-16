# flarum-money-erc20-deposit
Allow users to deposit ERC-20 tokens and receive Flarum money points. Powered by Alchemy webhooks. Supports Polygon and BSC.

# Money - ERC20 Deposit (Polygon/BSC)

A Flarum extension that allows users to deposit ERC-20 tokens and receive in-app money points.  
Perfect for communities using [shebaoting/money](https://discuss.flarum.org/d/35419-money-optimized).

- 🔗 **1 token = 1,000 money points**
- 🌐 Supports **Polygon** and **BSC**
- ⚡ Powered by **Alchemy webhooks**
- 🛡️ Secure deposit ID system
- 💬 Inspired by *In Time* movie

## Features

- ✅ Deposit ERC-20 tokens to earn in-app currency
- ✅ Automatic credit via Alchemy webhooks
- ✅ Prevents double-spends and fraud
- ✅ Logs all deposits
- ✅ Admin settings for rate, wallet, min deposit

## Installation

```bash
composer require shebaoting/money-erc20-deposit:"*"
php flarum migrate
php flarum cache:clear
