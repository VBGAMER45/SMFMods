================================================================================
 Army System 1.0 for SMF 2.1.x
 by: vbgamer45
 https://www.smfhacks.com
================================================================================

A multiplayer forum RPG where members build armies, pick races, buy weapons and
armor, upgrade fortifications and siege technology, train soldiers, hire
mercenaries, build a naval fleet, attack other players, spy on opponents, form
clans, and climb the rankings.

Based on the idea from the original IPB "Army System 2.2 FINAL" by supersmashbrothers.

================================================================================
 TABLE OF CONTENTS
================================================================================

 1. Requirements
 2. Installation
 3. Features Overview
 4. Race System
 5. Equipment & Items
 6. Training & Mercenaries
 7. Battle System
 8. Espionage System
 9. Naval Fleet (Ships)
10. Fortifications & Siege
11. Clans
12. Economy & Currency
13. Transfers
14. Vacation Mode
15. Rankings & Profiles
16. Admin Panel
17. Admin Settings Reference
18. Permissions
19. Scheduled Tasks
20. Uninstallation
21. Credits

================================================================================
 1. REQUIREMENTS
================================================================================

- SMF 2.1.x (tested with 2.1.0 through 2.1.6)
- PHP 8.0 or higher
- MySQL 5.7+ or MariaDB 10.x+
- InnoDB storage engine

================================================================================
 2. INSTALLATION
================================================================================

1. Download the Army System package (.tar.gz or .zip)
2. Go to Admin > Package Manager > Download Packages
3. Upload the package file
4. Click "Install Mod" and follow the prompts
5. Set permissions under Admin > Members > Permissions
6. Configure settings under Admin > Army System > Settings
7. The "Army" button will appear in the main navigation menu

================================================================================
 3. FEATURES OVERVIEW
================================================================================

The Army System adds a full RPG layer to your forum with these major systems:

- 10 unique races with strategic bonuses and tradeoffs
- 10 attack weapons and 10 defense armor pieces (tiered progression)
- 5 spy tools and 5 sentry tools
- 20 ships that boost both attack and defense
- 9-level fortification and 9-level siege upgrade trees
- 30-level unit production upgrades
- 10-level spy skill upgrades
- Soldier training (attack, defense, spy, sentry)
- Mercenary hiring (attack, defense, untrained)
- Full battle system with equipment degradation and casualties
- Espionage with recon and sabotage missions
- Clan system with leader management and join modes
- Gold/item transfers between players
- Vacation mode to protect inactive players
- Paginated rankings by army size
- Event feed tracking major actions
- Forum post integration (earn gold and soldiers from posting)
- Full admin panel with settings, race/item editors, member management
- IP-based multi-account detection
- Guest viewing support (configurable)

================================================================================
 4. RACE SYSTEM
================================================================================

New players must choose a race before participating. Each race provides
percentage bonuses (positive or negative) to different aspects of gameplay.
Choosing a race is permanent unless the player resets their army.

Default Races:

  Race        Income  Discount  Casualties  Attack  Defense  Spy  Sentry
  ----------  ------  --------  ----------  ------  -------  ---  ------
  Humans      +40%    0%        0%          0%      0%       0%   0%
  Elves       0%      0%        0%          +10%    +10%     0%   0%
  Orcs        0%      0%        0%          0%      0%       +10% +10%
  Ghosts      0%      0%        +25%        0%      0%       0%   0%
  Corpses     0%      0%        0%          +40%    -10%     -10% -5%
  Ogres       0%      0%        0%          -10%    +40%     -5%  -15%
  Wizards     0%      0%        0%          -15%    -5%      +40% -10%
  Wights      0%      0%        0%          -5%     -15%     -10% +40%
  Dragons     -50%    0%        0%          +15%    +15%     +15% +15%
  Dwarfs      0%      0%        +40%        -10%    0%       0%   0%

Bonus types explained:
- Income: Percentage modifier on gold generation
- Discount: Percentage reduction on item/training costs
- Casualties: Percentage reduction in soldiers lost during battle
- Attack/Defense/Spy/Sentry: Percentage modifier on respective power ratings

Admins can add, edit, or remove races from the admin panel. Each race can have
a custom icon image displayed throughout the interface.

================================================================================
 5. EQUIPMENT & ITEMS
================================================================================

Equipment is purchased from the Armory and contributes to power calculations.
Items have a strength value that degrades during combat (0.5% per turn used)
and can be repaired.

ATTACK WEAPONS (type 'a') — 10 tiers:
  #   Name               Strength    Price
  1   Dagger             10          10,000
  2   Hand Axe           30          30,000
  3   Spear              60          60,000
  4   Mace               100         100,000
  5   Flail              180         180,000
  6   Hammer             320         320,000
  7   War Hammer         600         600,000
  8   Battle Axe         1,200       1,200,000
  9   Morningstar        3,000       1,600,000
  10  Two Handed Sword   10,000      2,000,000

DEFENSE ARMOR (type 'd') — 10 tiers:
  #   Name               Strength    Price
  1   Leather Helmet     10          10,000
  2   Leather Armor      30          30,000
  3   Wooden Shield      60          60,000
  4   Scale Mail         100         100,000
  5   Iron Shield        180         180,000
  6   Plate Armor        320         320,000
  7   Steel Shield       600         600,000
  8   Steel Helmet       1,200       1,200,000
  9   Steel Plate Armor  3,000       1,600,000
  10  Elven Mail         10,000      2,000,000

SPY TOOLS (type 'q') — 5 tiers:
  #   Name               Strength    Price
  1   Rope               1           10,000
  2   Dirk               3           20,000
  3   Cloak              10          40,000
  4   Grappling Hook     25          60,000
  5   Stealth Horse      60          100,000

SENTRY TOOLS (type 'e') — 5 tiers:
  #   Name               Strength    Price
  1   Big Candle         1           10,000
  2   Horn               3           20,000
  3   Tripwire           10          40,000
  4   Guard Dog          25          60,000
  5   Watch Post         60          100,000

Items can be sold back at configurable resell rates:
- Armor: 75% of purchase price (default)
- Ships: 60% of purchase price (default)
- Weapons/Tools: 45% of purchase price (default)

All items have icon images displayed in the shop and inventory.

================================================================================
 6. TRAINING & MERCENARIES
================================================================================

TRAINING:
Untrained soldiers can be trained into specialized roles at the Training page.

  Type       Cost per Soldier
  ---------  ----------------
  Attack     2,500 gold
  Defense    2,500 gold
  Spy        3,500 gold
  Sentry     3,500 gold
  Untrain    500 gold (returns specialist to untrained pool)

UNIT PRODUCTION UPGRADES (30 levels):
Increase automatic soldier generation. Each level doubles the cost of the
previous level, starting at 10,000 gold.

MERCENARIES:
Hired soldiers that fight alongside your army. More expensive than training
but provide immediate power.

  Type       Cost     Strength
  ---------  ------   --------
  Attack     3,500    10 each
  Defense    3,500    10 each
  Untrained  3,000    25 each

Mercenaries require ongoing upkeep (default: 10 gold per merc, deducted by
scheduled task). Mercs can be retrained between roles.

================================================================================
 7. BATTLE SYSTEM
================================================================================

Players can attack others through the Attack page. Each attack costs turns.

ATTACK CALCULATION:
1. Attacker Offense =
   (weapon_strength + attack_soldiers + attack_mercs * 10 + ship_power / 2)
   * turns * (1 + race_attack_bonus%) * (1 + siege_bonus%)

2. Defender Defense =
   (armor_strength + defense_soldiers + defense_mercs * 10 + ship_power / 2)
   * (1 + race_defense_bonus%) * (1 + fort_bonus%)

3. Both values randomized +/- 20%
4. Higher total wins

BATTLE OUTCOMES:
- Gold stolen: defender_gold * (turns * attack_money_rate / 100)
- Casualties: Based on power ratio, army size, turns used, reduced by race bonus
- Equipment degradation: 0.5% strength loss per turn on weapons, armor, and ships

FLOOD PROTECTION:
- Maximum attacks against same target: 5 per 14-day period (configurable)
- IP tracking prevents multi-account abuse

BATTLE LOGS:
Full battle records including damage dealt, casualties on both sides, gold
stolen, and per-item degradation breakdown. Logs retained for 14 days
(configurable).

================================================================================
 8. ESPIONAGE SYSTEM
================================================================================

Players can run spy missions against other players through the Espionage page.

MISSION TYPES:
- Recon: Reveals target's army stats with an accuracy margin
- Sabotage: Damages random equipment in the target's inventory

SUCCESS CALCULATION:
  Spy Power = (spies_used + spy_tools) * (1 + race_spy_bonus%)
              * (1 + spy_skill_level * 10%)

  Sentry Power = (sentry_soldiers + sentry_tools) * (1 + race_sentry_bonus%)

  Success Chance = spy_power / (spy_power + sentry_power) * 100%

CONSEQUENCES:
- Failed missions: 1 to all deployed spies are killed
- Maximum spies per mission: 10 (configurable)

SPY SKILL UPGRADES (10 levels):
Each level adds +10% to spy power. Cost starts at 18,000 gold and doubles
per level.

================================================================================
 9. NAVAL FLEET (SHIPS)
================================================================================

Ships are a unique item type that contribute to BOTH attack and defense power
(50/50 split). They are purchased from the Ships tab in the Armory.

  #   Name                     Strength    Price
  1   Canoe                    5           8,000
  2   Rowboat                  10          15,000
  3   Skiff                    20          25,000
  4   Dhow                     40          40,000
  5   Fishing Trawler          75          70,000
  6   Merchant Cog             120         120,000
  7   Viking Longship          200         200,000
  8   Two-Masted Schooner      350         350,000
  9   Chinese Junk Warship     500         500,000
  10  Caravel                  750         750,000
  11  Carrack                  1,000       1,000,000
  12  Frigate                  1,500       1,500,000
  13  Small Warship            2,000       2,000,000
  14  Galleon                  3,000       3,000,000
  15  Man of War               5,000       5,000,000
  16  Icebreaker               7,000       7,000,000
  17  Elven Ship               9,000       9,000,000
  18  Ghost Ship               12,000      12,000,000
  19  Dwarven Submersible      15,000      15,000,000
  20  Airship                  20,000      20,000,000

Ships degrade in combat just like weapons and armor and can be repaired.
Ships can be sold at 60% of purchase price (configurable).

Naval Power is displayed on the dashboard alongside attack, defense, spy,
and sentry power ratings.

================================================================================
 10. FORTIFICATIONS & SIEGE
================================================================================

FORTIFICATIONS (9 levels):
Upgrade your fort to gain a defense bonus per level (default: 25% per level).

  Level  Name           Price
  1      Camp           Free (starting)
  2      Stockade       10,000
  3      Walled Town    20,000
  4      Tower          40,000
  5      Battlements    80,000
  6      Fortress       160,000
  7      Moat           320,000
  8      Stronghold     2,560,000
  9      Citadel        5,120,000

SIEGE TECHNOLOGY (9 levels):
Upgrade siege to gain an offense bonus against fortified defenders.

  Level  Name           Price
  1      None           Free (starting)
  2      Ballistas      10,000
  3      Battering Ram  20,000
  4      Ladders        40,000
  5      Catapult       80,000
  6      Siege Tower    160,000
  7      Trebuchets     320,000
  8      Dynamite       2,560,000
  9      Cannons        5,120,000

Both upgrade trees show visual progression indicators in the Armory.

================================================================================
 11. CLANS
================================================================================

Players can create or join clans for social organization.

FEATURES:
- Create a clan with a custom name and description
- Two join modes: Open (anyone can join) or Invite-Only
- Leader powers: invite members, approve/deny join requests, remove members,
  edit clan details, transfer leadership, disband clan
- Member roster with join dates
- Pending invitation/request management

REQUIREMENTS:
- Must have chosen a race to create or join a clan
- Cannot be in multiple clans simultaneously

================================================================================
 12. ECONOMY & CURRENCY
================================================================================

The currency name is fully customizable (default: "Gold").

GOLD GENERATION:
1. Forum posting (automatic, integrated with SMF):
   - New topic: 10,000 gold (configurable)
   - Reply: 5,000 gold (configurable)

2. Automatic income (scheduled task, every hour):
   - Base amount configurable (default: 50 gold per tick)
   - Modified by race income bonus
   - Modified by unit production level

SOLDIER GENERATION:
- Automatic via scheduled task based on unit production level
- Soldiers per post threshold (default: 10 soldiers per 10 posts)

EXPENSES:
- Equipment purchases (weapons, armor, tools, ships)
- Training costs
- Mercenary hiring and upkeep
- Fortification and siege upgrades
- Spy skill upgrades
- Unit production upgrades
- Viewing opponent stats before attack

================================================================================
 13. TRANSFERS
================================================================================

Players can send gold and items to other players through the Transfer page.

Default transfer services:
- Weapons Transfer: Send weapons to another player
- Money Transfer: Send gold to another player

Admins can define additional custom transfer services. All transfers are
logged in the transfer log for auditing.

================================================================================
 14. VACATION MODE
================================================================================

Players can activate vacation mode to protect themselves while away.

- Protected from all attacks while on vacation
- Cannot attack, spy, or perform most actions while on vacation
- Minimum duration: 4 days (configurable)
- Maximum duration: 28 days (configurable)
- Early return: Disabled by default (configurable)
- Vacation auto-expires when the end date is reached

================================================================================
 15. RANKINGS & PROFILES
================================================================================

RANKINGS:
- Paginated leaderboard sorted by army size
- Shows rank, player name, race, army size
- Accessible to guests (configurable)

PROFILES:
- "Army Stats" tab added to SMF user profiles
- Shows race (with icon), army size, rank, total attacks/defends
- Own profile shows additional details: gold, soldiers by type, turns
- Other players' profiles show limited public information
- Dedicated Army Profile page with full stat breakdown

DASHBOARD:
- Overview of all stats: race, army size, gold, rank
- Power ratings: attack, defense, spy, sentry, naval
- Soldier breakdown by type
- Recent events feed
- Quick action links to all major pages

================================================================================
 16. ADMIN PANEL
================================================================================

Access via Admin > Army System. Five sections:

SETTINGS:
All 40+ configurable options organized by category (see section 17 below).

RACES:
- View all races with their bonus values
- Add new races with custom bonuses and icons
- Edit existing race names, bonuses, and icons
- Delete races (with member impact warnings)

ITEMS:
- View all items filtered by type (weapons, armor, ships, etc.)
- Add new items with name, strength, price, and icon
- Edit item properties
- Delete items

MEMBERS:
- Search members by name
- View full army data for any member
- Edit member stats (gold, soldiers, turns, levels)
- Reset or deactivate member accounts

LOGS:
- Staff action audit trail (who changed what, when)
- Attack log viewer with filtering
- All admin actions are logged automatically

================================================================================
 17. ADMIN SETTINGS REFERENCE
================================================================================

GENERAL:
  army_enabled          Enable/disable the Army System          Default: On
  allow_guest_view      Allow guests to view rankings           Default: On
  name                  Custom name for the system              Default: Army System
  currency_name         Custom currency name                    Default: Gold

ECONOMY:
  tool_resell           Spy/sentry/weapon resell rate (%)       Default: 45%
  armor_resell          Armor resell rate (%)                   Default: 75%
  ship_resell           Ship resell rate (%)                    Default: 60%
  money_amount          Gold per automatic income tick          Default: 50
  money_mercanery       Mercenary upkeep cost per unit          Default: 10
  post_point_reply      Gold earned per forum reply             Default: 5,000
  post_point_topic      Gold earned per new topic               Default: 10,000
  guy_per_post          Soldiers gained per post threshold      Default: 10
  post_per_guy          Posts needed to reach threshold         Default: 10

COMBAT:
  max_attack            Max attacks on same target per period   Default: 5
  turns_max             Max turns per single attack             Default: 15
  turn_gain             Attack turns gained per tick            Default: 1
  attack_money          Gold stolen per attack (% per turn)     Default: 1%
  view_money            Gold cost to view opponent stats        Default: 5
  fort_percent          Fort defense bonus per level (%)        Default: 25%
  siege_percent         Siege offense bonus per level (%)       Default: 25%
  max_spy               Max spy soldiers per mission            Default: 10

PRODUCTION:
  auto_gain_prod        Enable automatic soldier generation     Default: On
  auto_gain_money       Enable automatic gold generation        Default: On
  production_type       Production formula type                 Default: 1
  production_base       Base production multiplier              Default: 2
  production_constant   Production constant                     Default: 1

STARTING RESOURCES (given on join/reset):
  reset_army            Starting untrained soldiers             Default: 10
  reset_turn            Starting attack turns                   Default: 25
  reset_money           Starting gold                           Default: 50,000

MAINTENANCE:
  log_time              Attack log retention period             Default: 14 days
  inactive_time         Inactivity threshold before flagging    Default: 14 days
  security_check        IP clone detection level (0=off)        Default: 0

VACATION:
  vacation_allowed      Enable vacation mode                    Default: On
  vacation_min_time     Minimum vacation duration (days)        Default: 4
  vacation_max_time     Maximum vacation duration (days)        Default: 28
  vacation_back         Allow early return from vacation        Default: Off

================================================================================
 18. PERMISSIONS
================================================================================

Five permissions are registered, configurable per membergroup:

  Permission      Description                                    Default
  --------------- ---------------------------------------------- -------
  army_view       View Army System pages and rankings             All
  army_play       Participate in Army System (join, buy, train)   Members
  army_attack     Execute attacks against other players           Members
  army_spy        Perform espionage missions                      Members
  army_admin      Access Army System admin panel                  Admin

================================================================================
 19. SCHEDULED TASKS
================================================================================

Three scheduled tasks are registered during installation:

  Task                    Interval    Description
  ----------------------  ----------  -----------------------------------------
  army_auto_gain          1 hour      Generate soldiers and gold for all players
  army_merc_upkeep        2 hours     Deduct mercenary maintenance costs
  army_inactive_check     1 day       Flag inactive players, handle cleanup

Tasks can be managed from Admin > Maintenance > Scheduled Tasks.

================================================================================
 20. UNINSTALLATION
================================================================================

1. Go to Admin > Package Manager > Installed Packages
2. Click "Uninstall" next to Army System
3. Follow the prompts

Uninstallation removes all source files, templates, CSS, language files,
images, hooks, and scheduled tasks. Database tables are NOT automatically
dropped to preserve data. To fully remove data, manually drop all tables
prefixed with {db_prefix}army_ from your database.

================================================================================
 21. CREDITS
================================================================================

- Based on the idea of IPB mod: supersmashbrothers (Army System 2.2 FINAL)
- SMF 2.1.x port: vbgamer45 (https://www.smfhacks.com)
- Icon artwork included in Themes/default/images/army/

================================================================================
