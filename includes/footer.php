    </main>

    <footer>
        <div class="footer-container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>AmiGo</h3>
                    <p><?php echo t('footer.share_activities'); ?></p>
                    <div class="footer-social">
                        <a href="#" aria-label="Facebook"><i class="social-icon">👍</i></a>
                        <a href="#" aria-label="Twitter"><i class="social-icon">🐦</i></a>
                        <a href="#" aria-label="Instagram"><i class="social-icon">📸</i></a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h4><?php echo t('footer.quick_links'); ?></h4>
                    <ul class="footer-links">
                        <li><a href="<?php echo isset($prefix) ? $prefix : '../'; ?>index.php">🏠 <?php echo t('footer.home'); ?></a></li>
                        <li><a href="<?php echo isset($prefix) ? $prefix : '../'; ?>events/events-list.php">🎉 <?php echo t('footer.events'); ?></a></li>
                        <li><a href="<?php echo isset($prefix) ? $prefix : '../'; ?>pages/forum.php">💬 <?php echo t('footer.forum'); ?></a></li>
                        <li><a href="<?php echo isset($prefix) ? $prefix : '../'; ?>pages/faq.php">❓ <?php echo t('footer.faq'); ?></a></li>
                        <li><a href="<?php echo isset($prefix) ? $prefix : '../'; ?>pages/contact.php">✉️ <?php echo t('footer.contact'); ?></a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4><?php echo t('footer.legal_info'); ?></h4>
                    <ul class="footer-links">
                        <li><a href="<?php echo isset($prefix) ? $prefix : '../'; ?>pages/cgu.php"><?php echo t('footer.cgu'); ?></a></li>
                        <li><a href="<?php echo isset($prefix) ? $prefix : '../'; ?>pages/mentions-legales.php"><?php echo t('footer.legal'); ?></a></li>
                        <li><a href="#"><?php echo t('footer.privacy'); ?></a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 AmiGo - <?php echo t('footer.all_rights_reserved'); ?></p>
            </div>
        </div>
    </footer>
</body>
</html>
