/*
 Navicat Premium Data Transfer

 Source Server         : ares
 Source Server Type    : MySQL
 Source Server Version : 50615 (5.6.15)
 Source Host           : 190.7.133.186:3310
 Source Schema         : test

 Target Server Type    : MySQL
 Target Server Version : 50615 (5.6.15)
 File Encoding         : 65001

 Date: 08/03/2024 12:41:19
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for direcciones_paises
-- ----------------------------
DROP TABLE IF EXISTS `direcciones_paises`;
CREATE TABLE `direcciones_paises`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code_phone` int(11) NOT NULL,
  `departamentos` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `direcciones_paises_code_unique`(`code`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 247 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of direcciones_paises
-- ----------------------------
INSERT INTO `direcciones_paises` VALUES (1, 'Afghanistan', 'AF', 93, 0);
INSERT INTO `direcciones_paises` VALUES (2, 'Albania', 'AL', 355, 0);
INSERT INTO `direcciones_paises` VALUES (3, 'Algeria', 'DZ', 213, 0);
INSERT INTO `direcciones_paises` VALUES (4, 'American Samoa', 'AS', 1684, 0);
INSERT INTO `direcciones_paises` VALUES (5, 'Andorra', 'AD', 376, 0);
INSERT INTO `direcciones_paises` VALUES (6, 'Angola', 'AO', 244, 0);
INSERT INTO `direcciones_paises` VALUES (7, 'Anguilla', 'AI', 1264, 0);
INSERT INTO `direcciones_paises` VALUES (8, 'Antarctica', 'AQ', 0, 0);
INSERT INTO `direcciones_paises` VALUES (9, 'Antigua And Barbuda', 'AG', 1268, 0);
INSERT INTO `direcciones_paises` VALUES (10, 'Argentina', 'AR', 54, 0);
INSERT INTO `direcciones_paises` VALUES (11, 'Armenia', 'AM', 374, 0);
INSERT INTO `direcciones_paises` VALUES (12, 'Aruba', 'AW', 297, 0);
INSERT INTO `direcciones_paises` VALUES (13, 'Australia', 'AU', 61, 0);
INSERT INTO `direcciones_paises` VALUES (14, 'Austria', 'AT', 43, 0);
INSERT INTO `direcciones_paises` VALUES (15, 'Azerbaijan', 'AZ', 994, 0);
INSERT INTO `direcciones_paises` VALUES (16, 'Bahamas The', 'BS', 1242, 0);
INSERT INTO `direcciones_paises` VALUES (17, 'Bahrain', 'BH', 973, 0);
INSERT INTO `direcciones_paises` VALUES (18, 'Bangladesh', 'BD', 880, 0);
INSERT INTO `direcciones_paises` VALUES (19, 'Barbados', 'BB', 1246, 0);
INSERT INTO `direcciones_paises` VALUES (20, 'Belarus', 'BY', 375, 0);
INSERT INTO `direcciones_paises` VALUES (21, 'Belgium', 'BE', 32, 0);
INSERT INTO `direcciones_paises` VALUES (22, 'Belize', 'BZ', 501, 0);
INSERT INTO `direcciones_paises` VALUES (23, 'Benin', 'BJ', 229, 0);
INSERT INTO `direcciones_paises` VALUES (24, 'Bermuda', 'BM', 1441, 0);
INSERT INTO `direcciones_paises` VALUES (25, 'Bhutan', 'BT', 975, 0);
INSERT INTO `direcciones_paises` VALUES (26, 'Bolivia', 'BO', 591, 1);
INSERT INTO `direcciones_paises` VALUES (27, 'Bosnia and Herzegovina', 'BA', 387, 0);
INSERT INTO `direcciones_paises` VALUES (28, 'Botswana', 'BW', 267, 0);
INSERT INTO `direcciones_paises` VALUES (29, 'Bouvet Island', 'BV', 0, 0);
INSERT INTO `direcciones_paises` VALUES (30, 'Brazil', 'BR', 55, 0);
INSERT INTO `direcciones_paises` VALUES (31, 'British Indian Ocean Territory', 'IO', 246, 0);
INSERT INTO `direcciones_paises` VALUES (32, 'Brunei', 'BN', 673, 0);
INSERT INTO `direcciones_paises` VALUES (33, 'Bulgaria', 'BG', 359, 0);
INSERT INTO `direcciones_paises` VALUES (34, 'Burkina Faso', 'BF', 226, 0);
INSERT INTO `direcciones_paises` VALUES (35, 'Burundi', 'BI', 257, 0);
INSERT INTO `direcciones_paises` VALUES (36, 'Cambodia', 'KH', 855, 0);
INSERT INTO `direcciones_paises` VALUES (37, 'Cameroon', 'CM', 237, 0);
INSERT INTO `direcciones_paises` VALUES (38, 'Canada', 'CA', 1, 0);
INSERT INTO `direcciones_paises` VALUES (39, 'Cape Verde', 'CV', 238, 0);
INSERT INTO `direcciones_paises` VALUES (40, 'Cayman Islands', 'KY', 1345, 0);
INSERT INTO `direcciones_paises` VALUES (41, 'Central African Republic', 'CF', 236, 0);
INSERT INTO `direcciones_paises` VALUES (42, 'Chad', 'TD', 235, 0);
INSERT INTO `direcciones_paises` VALUES (43, 'Chile', 'CL', 56, 0);
INSERT INTO `direcciones_paises` VALUES (44, 'China', 'CN', 86, 0);
INSERT INTO `direcciones_paises` VALUES (45, 'Christmas Island', 'CX', 61, 0);
INSERT INTO `direcciones_paises` VALUES (46, 'Cocos (Keeling) Islands', 'CC', 672, 0);
INSERT INTO `direcciones_paises` VALUES (47, 'Colombia', 'CO', 57, 1);
INSERT INTO `direcciones_paises` VALUES (48, 'Comoros', 'KM', 269, 0);
INSERT INTO `direcciones_paises` VALUES (49, 'Republic Of The Congo', 'CG', 242, 0);
INSERT INTO `direcciones_paises` VALUES (50, 'Democratic Republic Of The Congo', 'CD', 242, 0);
INSERT INTO `direcciones_paises` VALUES (51, 'Cook Islands', 'CK', 682, 0);
INSERT INTO `direcciones_paises` VALUES (52, 'Costa Rica', 'CR', 506, 0);
INSERT INTO `direcciones_paises` VALUES (53, 'Cote D\'Ivoire (Ivory Coast)', 'CI', 225, 0);
INSERT INTO `direcciones_paises` VALUES (54, 'Croatia (Hrvatska)', 'HR', 385, 0);
INSERT INTO `direcciones_paises` VALUES (55, 'Cuba', 'CU', 53, 0);
INSERT INTO `direcciones_paises` VALUES (56, 'Cyprus', 'CY', 357, 0);
INSERT INTO `direcciones_paises` VALUES (57, 'Czech Republic', 'CZ', 420, 0);
INSERT INTO `direcciones_paises` VALUES (58, 'Denmark', 'DK', 45, 0);
INSERT INTO `direcciones_paises` VALUES (59, 'Djibouti', 'DJ', 253, 0);
INSERT INTO `direcciones_paises` VALUES (60, 'Dominica', 'DM', 1767, 0);
INSERT INTO `direcciones_paises` VALUES (61, 'Dominican Republic', 'DO', 1809, 0);
INSERT INTO `direcciones_paises` VALUES (62, 'East Timor', 'TP', 670, 0);
INSERT INTO `direcciones_paises` VALUES (63, 'Ecuador', 'EC', 593, 0);
INSERT INTO `direcciones_paises` VALUES (64, 'Egypt', 'EG', 20, 0);
INSERT INTO `direcciones_paises` VALUES (65, 'El Salvador', 'SV', 503, 0);
INSERT INTO `direcciones_paises` VALUES (66, 'Equatorial Guinea', 'GQ', 240, 0);
INSERT INTO `direcciones_paises` VALUES (67, 'Eritrea', 'ER', 291, 0);
INSERT INTO `direcciones_paises` VALUES (68, 'Estonia', 'EE', 372, 0);
INSERT INTO `direcciones_paises` VALUES (69, 'Ethiopia', 'ET', 251, 0);
INSERT INTO `direcciones_paises` VALUES (70, 'External Territories of Australia', 'XA', 61, 0);
INSERT INTO `direcciones_paises` VALUES (71, 'Falkland Islands', 'FK', 500, 0);
INSERT INTO `direcciones_paises` VALUES (72, 'Faroe Islands', 'FO', 298, 0);
INSERT INTO `direcciones_paises` VALUES (73, 'Fiji Islands', 'FJ', 679, 0);
INSERT INTO `direcciones_paises` VALUES (74, 'Finland', 'FI', 358, 0);
INSERT INTO `direcciones_paises` VALUES (75, 'France', 'FR', 33, 0);
INSERT INTO `direcciones_paises` VALUES (76, 'French Guiana', 'GF', 594, 0);
INSERT INTO `direcciones_paises` VALUES (77, 'French Polynesia', 'PF', 689, 0);
INSERT INTO `direcciones_paises` VALUES (78, 'French Southern Territories', 'TF', 0, 0);
INSERT INTO `direcciones_paises` VALUES (79, 'Gabon', 'GA', 241, 0);
INSERT INTO `direcciones_paises` VALUES (80, 'Gambia The', 'GM', 220, 0);
INSERT INTO `direcciones_paises` VALUES (81, 'Georgia', 'GE', 995, 0);
INSERT INTO `direcciones_paises` VALUES (82, 'Germany', 'DE', 49, 0);
INSERT INTO `direcciones_paises` VALUES (83, 'Ghana', 'GH', 233, 0);
INSERT INTO `direcciones_paises` VALUES (84, 'Gibraltar', 'GI', 350, 0);
INSERT INTO `direcciones_paises` VALUES (85, 'Greece', 'GR', 30, 0);
INSERT INTO `direcciones_paises` VALUES (86, 'Greenland', 'GL', 299, 0);
INSERT INTO `direcciones_paises` VALUES (87, 'Grenada', 'GD', 1473, 0);
INSERT INTO `direcciones_paises` VALUES (88, 'Guadeloupe', 'GP', 590, 0);
INSERT INTO `direcciones_paises` VALUES (89, 'Guam', 'GU', 1671, 0);
INSERT INTO `direcciones_paises` VALUES (90, 'Guatemala', 'GT', 502, 0);
INSERT INTO `direcciones_paises` VALUES (91, 'Guernsey and Alderney', 'XU', 44, 0);
INSERT INTO `direcciones_paises` VALUES (92, 'Guinea', 'GN', 224, 0);
INSERT INTO `direcciones_paises` VALUES (93, 'Guinea-Bissau', 'GW', 245, 0);
INSERT INTO `direcciones_paises` VALUES (94, 'Guyana', 'GY', 592, 0);
INSERT INTO `direcciones_paises` VALUES (95, 'Haiti', 'HT', 509, 0);
INSERT INTO `direcciones_paises` VALUES (96, 'Heard and McDonald Islands', 'HM', 0, 0);
INSERT INTO `direcciones_paises` VALUES (97, 'Honduras', 'HN', 504, 0);
INSERT INTO `direcciones_paises` VALUES (98, 'Hong Kong S.A.R.', 'HK', 852, 0);
INSERT INTO `direcciones_paises` VALUES (99, 'Hungary', 'HU', 36, 0);
INSERT INTO `direcciones_paises` VALUES (100, 'Iceland', 'IS', 354, 0);
INSERT INTO `direcciones_paises` VALUES (101, 'India', 'IN', 91, 0);
INSERT INTO `direcciones_paises` VALUES (102, 'Indonesia', 'ID', 62, 0);
INSERT INTO `direcciones_paises` VALUES (103, 'Iran', 'IR', 98, 0);
INSERT INTO `direcciones_paises` VALUES (104, 'Iraq', 'IQ', 964, 0);
INSERT INTO `direcciones_paises` VALUES (105, 'Ireland', 'IE', 353, 0);
INSERT INTO `direcciones_paises` VALUES (106, 'Israel', 'IL', 972, 0);
INSERT INTO `direcciones_paises` VALUES (107, 'Italy', 'IT', 39, 0);
INSERT INTO `direcciones_paises` VALUES (108, 'Jamaica', 'JM', 1876, 0);
INSERT INTO `direcciones_paises` VALUES (109, 'Japan', 'JP', 81, 0);
INSERT INTO `direcciones_paises` VALUES (110, 'Jersey', 'XJ', 44, 0);
INSERT INTO `direcciones_paises` VALUES (111, 'Jordan', 'JO', 962, 0);
INSERT INTO `direcciones_paises` VALUES (112, 'Kazakhstan', 'KZ', 7, 0);
INSERT INTO `direcciones_paises` VALUES (113, 'Kenya', 'KE', 254, 0);
INSERT INTO `direcciones_paises` VALUES (114, 'Kiribati', 'KI', 686, 0);
INSERT INTO `direcciones_paises` VALUES (115, 'Korea North', 'KP', 850, 0);
INSERT INTO `direcciones_paises` VALUES (116, 'Korea South', 'KR', 82, 0);
INSERT INTO `direcciones_paises` VALUES (117, 'Kuwait', 'KW', 965, 0);
INSERT INTO `direcciones_paises` VALUES (118, 'Kyrgyzstan', 'KG', 996, 0);
INSERT INTO `direcciones_paises` VALUES (119, 'Laos', 'LA', 856, 0);
INSERT INTO `direcciones_paises` VALUES (120, 'Latvia', 'LV', 371, 0);
INSERT INTO `direcciones_paises` VALUES (121, 'Lebanon', 'LB', 961, 0);
INSERT INTO `direcciones_paises` VALUES (122, 'Lesotho', 'LS', 266, 0);
INSERT INTO `direcciones_paises` VALUES (123, 'Liberia', 'LR', 231, 0);
INSERT INTO `direcciones_paises` VALUES (124, 'Libya', 'LY', 218, 0);
INSERT INTO `direcciones_paises` VALUES (125, 'Liechtenstein', 'LI', 423, 0);
INSERT INTO `direcciones_paises` VALUES (126, 'Lithuania', 'LT', 370, 0);
INSERT INTO `direcciones_paises` VALUES (127, 'Luxembourg', 'LU', 352, 0);
INSERT INTO `direcciones_paises` VALUES (128, 'Macau S.A.R.', 'MO', 853, 0);
INSERT INTO `direcciones_paises` VALUES (129, 'Macedonia', 'MK', 389, 0);
INSERT INTO `direcciones_paises` VALUES (130, 'Madagascar', 'MG', 261, 0);
INSERT INTO `direcciones_paises` VALUES (131, 'Malawi', 'MW', 265, 0);
INSERT INTO `direcciones_paises` VALUES (132, 'Malaysia', 'MY', 60, 0);
INSERT INTO `direcciones_paises` VALUES (133, 'Maldives', 'MV', 960, 0);
INSERT INTO `direcciones_paises` VALUES (134, 'Mali', 'ML', 223, 0);
INSERT INTO `direcciones_paises` VALUES (135, 'Malta', 'MT', 356, 0);
INSERT INTO `direcciones_paises` VALUES (136, 'Man (Isle of)', 'XM', 44, 0);
INSERT INTO `direcciones_paises` VALUES (137, 'Marshall Islands', 'MH', 692, 0);
INSERT INTO `direcciones_paises` VALUES (138, 'Martinique', 'MQ', 596, 0);
INSERT INTO `direcciones_paises` VALUES (139, 'Mauritania', 'MR', 222, 0);
INSERT INTO `direcciones_paises` VALUES (140, 'Mauritius', 'MU', 230, 0);
INSERT INTO `direcciones_paises` VALUES (141, 'Mayotte', 'YT', 269, 0);
INSERT INTO `direcciones_paises` VALUES (142, 'Mexico', 'MX', 52, 0);
INSERT INTO `direcciones_paises` VALUES (143, 'Micronesia', 'FM', 691, 0);
INSERT INTO `direcciones_paises` VALUES (144, 'Moldova', 'MD', 373, 0);
INSERT INTO `direcciones_paises` VALUES (145, 'Monaco', 'MC', 377, 0);
INSERT INTO `direcciones_paises` VALUES (146, 'Mongolia', 'MN', 976, 0);
INSERT INTO `direcciones_paises` VALUES (147, 'Montserrat', 'MS', 1664, 0);
INSERT INTO `direcciones_paises` VALUES (148, 'Morocco', 'MA', 212, 0);
INSERT INTO `direcciones_paises` VALUES (149, 'Mozambique', 'MZ', 258, 0);
INSERT INTO `direcciones_paises` VALUES (150, 'Myanmar', 'MM', 95, 0);
INSERT INTO `direcciones_paises` VALUES (151, 'Namibia', 'NA', 264, 0);
INSERT INTO `direcciones_paises` VALUES (152, 'Nauru', 'NR', 674, 0);
INSERT INTO `direcciones_paises` VALUES (153, 'Nepal', 'NP', 977, 0);
INSERT INTO `direcciones_paises` VALUES (154, 'Netherlands Antilles', 'AN', 599, 0);
INSERT INTO `direcciones_paises` VALUES (155, 'Netherlands The', 'NL', 31, 0);
INSERT INTO `direcciones_paises` VALUES (156, 'New Caledonia', 'NC', 687, 0);
INSERT INTO `direcciones_paises` VALUES (157, 'New Zealand', 'NZ', 64, 0);
INSERT INTO `direcciones_paises` VALUES (158, 'Nicaragua', 'NI', 505, 0);
INSERT INTO `direcciones_paises` VALUES (159, 'Niger', 'NE', 227, 0);
INSERT INTO `direcciones_paises` VALUES (160, 'Nigeria', 'NG', 234, 0);
INSERT INTO `direcciones_paises` VALUES (161, 'Niue', 'NU', 683, 0);
INSERT INTO `direcciones_paises` VALUES (162, 'Norfolk Island', 'NF', 672, 0);
INSERT INTO `direcciones_paises` VALUES (163, 'Northern Mariana Islands', 'MP', 1670, 0);
INSERT INTO `direcciones_paises` VALUES (164, 'Norway', 'NO', 47, 0);
INSERT INTO `direcciones_paises` VALUES (165, 'Oman', 'OM', 968, 0);
INSERT INTO `direcciones_paises` VALUES (166, 'Pakistan', 'PK', 92, 0);
INSERT INTO `direcciones_paises` VALUES (167, 'Palau', 'PW', 680, 0);
INSERT INTO `direcciones_paises` VALUES (168, 'Palestinian Territory Occupied', 'PS', 970, 0);
INSERT INTO `direcciones_paises` VALUES (169, 'Panama', 'PA', 507, 0);
INSERT INTO `direcciones_paises` VALUES (170, 'Papua new Guinea', 'PG', 675, 0);
INSERT INTO `direcciones_paises` VALUES (171, 'Paraguay', 'PY', 595, 0);
INSERT INTO `direcciones_paises` VALUES (172, 'Peru', 'PE', 51, 0);
INSERT INTO `direcciones_paises` VALUES (173, 'Philippines', 'PH', 63, 0);
INSERT INTO `direcciones_paises` VALUES (174, 'Pitcairn Island', 'PN', 0, 0);
INSERT INTO `direcciones_paises` VALUES (175, 'Poland', 'PL', 48, 0);
INSERT INTO `direcciones_paises` VALUES (176, 'Portugal', 'PT', 351, 0);
INSERT INTO `direcciones_paises` VALUES (177, 'Puerto Rico', 'PR', 1787, 0);
INSERT INTO `direcciones_paises` VALUES (178, 'Qatar', 'QA', 974, 0);
INSERT INTO `direcciones_paises` VALUES (179, 'Reunion', 'RE', 262, 0);
INSERT INTO `direcciones_paises` VALUES (180, 'Romania', 'RO', 40, 0);
INSERT INTO `direcciones_paises` VALUES (181, 'Russia', 'RU', 70, 0);
INSERT INTO `direcciones_paises` VALUES (182, 'Rwanda', 'RW', 250, 0);
INSERT INTO `direcciones_paises` VALUES (183, 'Saint Helena', 'SH', 290, 0);
INSERT INTO `direcciones_paises` VALUES (184, 'Saint Kitts And Nevis', 'KN', 1869, 0);
INSERT INTO `direcciones_paises` VALUES (185, 'Saint Lucia', 'LC', 1758, 0);
INSERT INTO `direcciones_paises` VALUES (186, 'Saint Pierre and Miquelon', 'PM', 508, 0);
INSERT INTO `direcciones_paises` VALUES (187, 'Saint Vincent And The Grenadines', 'VC', 1784, 0);
INSERT INTO `direcciones_paises` VALUES (188, 'Samoa', 'WS', 684, 0);
INSERT INTO `direcciones_paises` VALUES (189, 'San Marino', 'SM', 378, 0);
INSERT INTO `direcciones_paises` VALUES (190, 'Sao Tome and Principe', 'ST', 239, 0);
INSERT INTO `direcciones_paises` VALUES (191, 'Saudi Arabia', 'SA', 966, 0);
INSERT INTO `direcciones_paises` VALUES (192, 'Senegal', 'SN', 221, 0);
INSERT INTO `direcciones_paises` VALUES (193, 'Serbia', 'RS', 381, 0);
INSERT INTO `direcciones_paises` VALUES (194, 'Seychelles', 'SC', 248, 0);
INSERT INTO `direcciones_paises` VALUES (195, 'Sierra Leone', 'SL', 232, 0);
INSERT INTO `direcciones_paises` VALUES (196, 'Singapore', 'SG', 65, 0);
INSERT INTO `direcciones_paises` VALUES (197, 'Slovakia', 'SK', 421, 0);
INSERT INTO `direcciones_paises` VALUES (198, 'Slovenia', 'SI', 386, 0);
INSERT INTO `direcciones_paises` VALUES (199, 'Smaller Territories of the UK', 'XG', 44, 0);
INSERT INTO `direcciones_paises` VALUES (200, 'Solomon Islands', 'SB', 677, 0);
INSERT INTO `direcciones_paises` VALUES (201, 'Somalia', 'SO', 252, 0);
INSERT INTO `direcciones_paises` VALUES (202, 'South Africa', 'ZA', 27, 0);
INSERT INTO `direcciones_paises` VALUES (203, 'South Georgia', 'GS', 0, 0);
INSERT INTO `direcciones_paises` VALUES (204, 'South Sudan', 'SS', 211, 0);
INSERT INTO `direcciones_paises` VALUES (205, 'Spain', 'ES', 34, 0);
INSERT INTO `direcciones_paises` VALUES (206, 'Sri Lanka', 'LK', 94, 0);
INSERT INTO `direcciones_paises` VALUES (207, 'Sudan', 'SD', 249, 0);
INSERT INTO `direcciones_paises` VALUES (208, 'Suriname', 'SR', 597, 0);
INSERT INTO `direcciones_paises` VALUES (209, 'Svalbard And Jan Mayen Islands', 'SJ', 47, 0);
INSERT INTO `direcciones_paises` VALUES (210, 'Swaziland', 'SZ', 268, 0);
INSERT INTO `direcciones_paises` VALUES (211, 'Sweden', 'SE', 46, 0);
INSERT INTO `direcciones_paises` VALUES (212, 'Switzerland', 'CH', 41, 0);
INSERT INTO `direcciones_paises` VALUES (213, 'Syria', 'SY', 963, 0);
INSERT INTO `direcciones_paises` VALUES (214, 'Taiwan', 'TW', 886, 0);
INSERT INTO `direcciones_paises` VALUES (215, 'Tajikistan', 'TJ', 992, 0);
INSERT INTO `direcciones_paises` VALUES (216, 'Tanzania', 'TZ', 255, 0);
INSERT INTO `direcciones_paises` VALUES (217, 'Thailand', 'TH', 66, 0);
INSERT INTO `direcciones_paises` VALUES (218, 'Togo', 'TG', 228, 0);
INSERT INTO `direcciones_paises` VALUES (219, 'Tokelau', 'TK', 690, 0);
INSERT INTO `direcciones_paises` VALUES (220, 'Tonga', 'TO', 676, 0);
INSERT INTO `direcciones_paises` VALUES (221, 'Trinidad And Tobago', 'TT', 1868, 0);
INSERT INTO `direcciones_paises` VALUES (222, 'Tunisia', 'TN', 216, 0);
INSERT INTO `direcciones_paises` VALUES (223, 'Turkey', 'TR', 90, 0);
INSERT INTO `direcciones_paises` VALUES (224, 'Turkmenistan', 'TM', 7370, 0);
INSERT INTO `direcciones_paises` VALUES (225, 'Turks And Caicos Islands', 'TC', 1649, 0);
INSERT INTO `direcciones_paises` VALUES (226, 'Tuvalu', 'TV', 688, 0);
INSERT INTO `direcciones_paises` VALUES (227, 'Uganda', 'UG', 256, 0);
INSERT INTO `direcciones_paises` VALUES (228, 'Ukraine', 'UA', 380, 0);
INSERT INTO `direcciones_paises` VALUES (229, 'United Arab Emirates', 'AE', 971, 0);
INSERT INTO `direcciones_paises` VALUES (230, 'United Kingdom', 'GB', 44, 0);
INSERT INTO `direcciones_paises` VALUES (231, 'United States', 'US', 1, 0);
INSERT INTO `direcciones_paises` VALUES (232, 'United States Minor Outlying Islands', 'UM', 1, 0);
INSERT INTO `direcciones_paises` VALUES (233, 'Uruguay', 'UY', 598, 0);
INSERT INTO `direcciones_paises` VALUES (234, 'Uzbekistan', 'UZ', 998, 0);
INSERT INTO `direcciones_paises` VALUES (235, 'Vanuatu', 'VU', 678, 0);
INSERT INTO `direcciones_paises` VALUES (236, 'Vatican City State (Holy See)', 'VA', 39, 0);
INSERT INTO `direcciones_paises` VALUES (237, 'Venezuela', 'VE', 58, 0);
INSERT INTO `direcciones_paises` VALUES (238, 'Vietnam', 'VN', 84, 0);
INSERT INTO `direcciones_paises` VALUES (239, 'Virgin Islands (British)', 'VG', 1284, 0);
INSERT INTO `direcciones_paises` VALUES (240, 'Virgin Islands (US)', 'VI', 1340, 0);
INSERT INTO `direcciones_paises` VALUES (241, 'Wallis And Futuna Islands', 'WF', 681, 0);
INSERT INTO `direcciones_paises` VALUES (242, 'Western Sahara', 'EH', 212, 0);
INSERT INTO `direcciones_paises` VALUES (243, 'Yemen', 'YE', 967, 0);
INSERT INTO `direcciones_paises` VALUES (244, 'Yugoslavia', 'YU', 38, 0);
INSERT INTO `direcciones_paises` VALUES (245, 'Zambia', 'ZM', 260, 0);
INSERT INTO `direcciones_paises` VALUES (246, 'Zimbabwe', 'ZW', 263, 0);

SET FOREIGN_KEY_CHECKS = 1;
