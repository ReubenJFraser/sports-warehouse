# Ryderwear Duplicate-Collision Split-Path Summary

## Scope
- This artifact is planning-only and non-destructive.
- No MySQL, ProductDB, code, publication, or file operations are authorized by this proposal.

## Coverage totals
- total rows reviewed: 12
- total collision groups: 7

## Count by recommended_action
- keep_existing_destination_owner: 1
- needs_manual_review: 1
- propose_split_destination: 10

## Count by confidence
- high: 3
- low: 1
- medium: 8

## Proposed split paths by collision group
### group-1-activate-vs-momentum
- itemId 138: `images/brands/ryderwear/women/non-nkd/tops/sports-bra/cut/twist/--collection/activate/twist/black` (propose_split_destination)
### group-2-lift-pair
- itemId 153: `images/brands/ryderwear/women/non-nkd/tops/sports-bra/cut/halter/seamless/fabric/rib/--collection/lift-2-0/black` (propose_split_destination)
- itemId 154: `images/brands/ryderwear/women/non-nkd/tops/sports-bra/cut/halter/seamless/fabric/rib/--collection/rib-seamless-halter/black` (propose_split_destination)
### group-3-nkd-staples-cut-split
- itemId 157: `images/brands/ryderwear/women/nkd/tops/sports-bra/cut/low-support/bandeau/black` (propose_split_destination)
- itemId 163: `images/brands/ryderwear/women/nkd/tops/sports-bra/cut/low-support/one-shoulder/black` (propose_split_destination)
### group-4-core-vs-embody
- itemId 158: `images/brands/ryderwear/women/nkd/tops/sports-bra/cut/light-support/core/blue` (propose_split_destination)
- itemId 160: `images/brands/ryderwear/women/nkd/tops/sports-bra/cut/light-support/embody/blue` (keep_existing_destination_owner)
### group-5-knot-vs-twist-cross-over
- itemId 162: `images/brands/ryderwear/women/nkd/tops/sports-bra/cut/halter/construction/tank/knot/white` (propose_split_destination)
- itemId 174: `images/brands/ryderwear/women/nkd/tops/sports-bra/cut/halter/construction/tank/twist/white` (propose_split_destination)
### group-6-scrunch-v-halter-vs-underwire-keyhole
- itemId 166: `images/brands/ryderwear/women/nkd/tops/sports-bra/cut/halter/construction/scrunch-v-halter/bra/espresso` (propose_split_destination)
- itemId 176: `images/brands/ryderwear/women/nkd/tops/sports-bra/cut/halter/construction/underwire-keyhole/bra/espresso` (propose_split_destination)
### group-7-sculpt-multi-competitor
- itemId 184: `images/brands/ryderwear/women/non-nkd/tops/sports-bra/cut/halter/seamless/cut/low-support/--collection/sculpt-seamless-halter/azure` (needs_manual_review)

## Rows still requiring manual review
- itemId 184 (Sculpt Seamless Halter Sports Bra): action `needs_manual_review`, confidence `low`.

## Safety statement
This split-path proposal is not approval to copy images, update ProductDB fields, update MySQL fields, or execute publication workflow steps.

## Recommended next task after human approval
Create a follow-on **non-destructive destination ownership + copy simulation worksheet** that applies approved paths, re-checks destination collisions, and only then prepares a separate review artifact for any future DB/ProductDB update planning.
