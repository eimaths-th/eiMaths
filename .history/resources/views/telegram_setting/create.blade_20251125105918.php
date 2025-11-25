<div class="modal fade" id="CreateModal" tabindex="-1" role="dialog" aria-labelledby="CreateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title" id="CreateModalLabel"><i class="fab fa-telegram"></i> Add Telegram Setting</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="CreateForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="bot_token">Bot Token <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="bot_token" name="bot_token"
                                    placeholder="เช่น 123456789:ABCdefGHIjklMNOpqrsTUVwxyz" required>
                                <small class="form-text text-muted">Token ที่ได้จาก @BotFather</small>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="chat_id">Chat ID <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="chat_id" name="chat_id"
                                    placeholder="เช่น -1001234567890 หรือ 123456789" required>
                                <small class="form-text text-muted">Chat ID ของ Group/Channel หรือ User ID (ถ้าเป็น Group จะขึ้นต้นด้วย -)</small>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="description">Description</label>
                                <input type="text" class="form-control" id="description" name="description"
                                    placeholder="เช่น Admin Notification Group">
                                <small class="form-text text-muted">คำอธิบายสำหรับการตั้งค่านี้ (ไม่บังคับ)</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
